<?php
namespace Bookly\Lib\Base;

/**
 * Class Updater
 * @package Bookly\Lib\Base
 */
abstract class Updater extends Schema
{
    /**
     * Run updates on 'plugins_loaded' hook.
     */
    public function run()
    {
        $plugin_class   = Plugin::getPluginFor( $this );
        $transient_name = $plugin_class::getPrefix() . 'updating_db';
        $lock           = (int) get_transient( $transient_name );
        if ( $lock + 30 < time() ) {
            // Lock concurrent updates for 30 seconds.
            set_transient( $transient_name, time() );
            $version_option_name = $plugin_class::getPrefix() . 'db_version';
            $db_version          = get_option( $version_option_name );
            $plugin_version      = $plugin_class::getVersion();
            if ( $db_version !== false && version_compare( $plugin_version, $db_version, '>' ) ) {
                set_time_limit( 0 );

                $updates = array_filter(
                    get_class_methods( $this ),
                    function ( $method ) { return strstr( $method, 'update_' ); }
                );
                usort( $updates, 'strnatcmp' );

                foreach ( $updates as $method ) {
                    $version = str_replace( '_', '.', substr( $method, 7 ) );
                    if ( strnatcmp( $version, $db_version ) > 0 && strnatcmp( $version, $plugin_version ) <= 0 ) {
                        // Update the lock.
                        set_transient( $transient_name, time() );
                        // Do update.
                        call_user_func( array( $this, $method ) );
                        update_option( $version_option_name, $version );
                    }
                }
                // Make sure db_version is set to plugin version (even though there were no updates).
                update_option( $version_option_name, $plugin_version );
            }
            // Remove the lock.
            delete_transient( $transient_name );
        }
    }

    /**
     * Execute array queries where the key is the table name.
     *
     * @param array $data key is table name
     */
    protected function alterTables( array $data )
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ( $data as $table => $queries ) {
            $table_name = $this->getTableName( $table );
            foreach ( $queries as $query ) {
                $wpdb->query( sprintf( $query, $table_name ) );
            }
        }
    }

    /**
     * Rename options.
     *
     * @param array $options
     */
    protected function renameOptions( array $options )
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ( $options as $old_name => $new_name ) {
            $wpdb->query( $wpdb->prepare(
                'UPDATE `' . $wpdb->options . '` SET `option_name` = %s WHERE `option_name` = %s',
                $new_name,
                $old_name
            ) );
        }
    }

    /**
     * Rename user meta keys.
     *
     * @param array $meta
     */
    protected function renameUserMeta( array $meta )
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ( $meta as $old_name => $new_name ) {
            $wpdb->query( $wpdb->prepare(
                'UPDATE `' . $wpdb->usermeta . '` SET `meta_key` = %s WHERE `meta_key` = %s',
                $new_name,
                $old_name
            ) );
        }
    }

    /**
     * Update user meta.
     *
     * @param array $meta
     */
    protected function updateUserMeta( array $meta )
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        foreach ( $meta as $meta_key => $value ) {
            $wpdb->query( $wpdb->prepare(
                'UPDATE `' . $wpdb->usermeta . '` SET `meta_value` = %s WHERE `meta_key` = %s',
                $value,
                $meta_key
            ) );
        }
    }

    /**
     * Add options and register corresponding WPML strings.
     *
     * @param array $options
     */
    protected function addL10nOptions( array $options )
    {
        foreach ( $options as $option_name => $option_value ) {
            add_option( $option_name, $option_value );
            do_action( 'wpml_register_single_string', 'bookly', $option_name, $option_value );
        }
    }

    /**
     * This method allows one-time code execution,
     * at multiple calls to the same update_ * method, (for example, in case of timeout)
     *
     * @param string   $token
     * @param callable $callable
     * @return string
     */
    protected function disposable( $token, $callable )
    {
        $disposable_key = strtolower( strtok( __NAMESPACE__, '\\' ) ) . '_disposable_' . $token . '_completed';
        $completed      = (int) get_option( $disposable_key );
        if ( $completed === 0 ) {
            call_user_func( $callable );
            add_option( $disposable_key, '1' );
        }

        return $disposable_key;
    }

    /**
     * Rename WPML strings.
     *
     * @param array $strings
     * @param bool $rename_options
     */
    protected function renameL10nStrings( array $strings, $rename_options = true )
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        if ( $this->tableExists( 'icl_strings' ) ) {
            $wpml_strings_table = $this->getTableName( 'icl_strings' );
            // Check that `domain_name_context_md5` column exists.
            $exists = $wpdb->query( $wpdb->prepare(
                'SELECT 1 FROM `information_schema`.`columns`
                    WHERE `column_name`  = "domain_name_context_md5"
                      AND `table_name`   = %s
                      AND `table_schema` = SCHEMA()
                    LIMIT 1',
                $wpml_strings_table
            ) );
            if ( $exists ) {
                foreach ( $strings as $old_name => $new_name ) {
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE `$wpml_strings_table`
                          SET `name` = %s, `domain_name_context_md5` = MD5(CONCAT(`context`, %s, `gettext_context`))
                          WHERE `name` = %s",
                        $new_name,
                        $new_name,
                        $old_name
                    ) );
                }
            } else {
                foreach ( $strings as $old_name => $new_name ) {
                    $wpdb->query( $wpdb->prepare(
                        "UPDATE `$wpml_strings_table` SET `name` = %s WHERE `name` = %s",
                        $new_name,
                        $old_name
                    ) );
                }
            }
        }

        if ( $rename_options ) {
            $this->renameOptions( $strings );
        }
    }

    /**
     * Upgrade character and collate for bookly tables.
     *
     * @param array $tables
     */
    protected function upgradeCharsetCollate( array $tables )
    {
        global $wpdb;
        // In WordPress 4.2, team upgraded wp tables to utf8mb4.
        if ( $wpdb->has_cap( 'collation' ) ) {
            // Bookly < 17.3 by default used CHARACTER SET = utf8 COLLATE = utf8_general_ci
            // mysql 5.5.3+ (2010) support utf8mb4
            // mysql 5.6+          support utf8mb4_520
            if ( $wpdb->charset ) {
                $query = sprintf( 'SELECT TABLE_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS
                   WHERE TABLE_NAME IN (%s)
                     AND TABLE_SCHEMA = SCHEMA()
                     AND CHARACTER_SET_NAME IS NOT NULL
                     AND CHARACTER_SET_NAME != %%s',
                    implode( ', ', array_fill( 0, count( $tables ), '%s' ) )
                );
                $alter  = 'ALTER TABLE `%s` CONVERT TO CHARACTER SET ' . $wpdb->charset;
                $params = array_map( array( $this, 'getTableName' ), $tables );
                $params[] = $wpdb->charset;
                if ( $wpdb->collate ) {
                    $query .= '
                     AND COLLATION_NAME IS NOT NULL
                     AND COLLATION_NAME != %s';
                    $params[] = $wpdb->collate;
                    $alter .=' COLLATE ' . $wpdb->collate;
                }

                $records = $wpdb->get_results( $wpdb->prepare( $query, $params ) );
                foreach ( $records as $record ) {
                    $wpdb->query( sprintf( $alter, $record->TABLE_NAME ) );
                }
            }
        }
    }

}