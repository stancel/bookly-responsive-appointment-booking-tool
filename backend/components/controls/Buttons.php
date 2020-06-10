<?php
namespace Bookly\Backend\Components\Controls;

use Bookly\Lib\Plugin;

/**
 * Class Buttons
 * @package Bookly\Backend\Components\Controls
 */
class Buttons
{
    /**
     * Render button.
     *
     * @param string $id
     * @param string $class
     * @param string $caption
     * @param array  $attrs
     * @param string $caption_template
     */
    public static function render( $id = null, $class = null, $caption = null, array $attrs = array(), $caption_template = '{caption}' )
    {
        echo self::_createButton( 'button', $id, $class, null, $attrs, $caption, $caption_template );
    }

    /**
     * Render default.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attrs
     * @param bool $ellipsis
     */
    public static function renderDefault( $id = null, $extra_class = null, $caption = null, array $attrs = array(), $ellipsis = false )
    {
        echo self::_createButton(
            'button',
            $id,
            'btn-default',
            $extra_class,
            $attrs,
            $caption,
            '{caption}' . ( $ellipsis ? '…' : '' )
        );
    }

    /**
     * Render Add button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attrs
     * @param bool $ellipsis
     */
    public static function renderAdd( $id = 'bookly-add', $extra_class = null, $caption = null, array $attrs = array(), $ellipsis = true )
    {
        echo self::_createButton(
            'button',
            $id,
            'btn-success',
            $extra_class,
            $attrs,
            $caption !== null ? $caption : __( 'Add', 'bookly' ),
            '<i class="fas fa-fw fa-plus mr-1"></i>{caption}' . ( $ellipsis ? '…' : '' )
        );
    }

    /**
     * Render delete button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attrs
     * @param bool $ellipsis
     */
    public static function renderDelete( $id = 'bookly-delete', $extra_class = null, $caption = null, array $attrs = array(), $ellipsis = true )
    {
        echo self::_createButton(
            'button',
            $id,
            'btn-danger',
            $extra_class,
            $attrs,
            $caption !== null ? $caption : __( 'Delete', 'bookly' ),
            '<i class="far fa-fw fa-trash-alt mr-1"></i>{caption}' . ( $ellipsis ? '…' : '' )
        );
    }

    /**
     * Render reset button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attrs
     * @param bool $ellipsis
     */
    public static function renderReset( $id = null, $extra_class = null, $caption = null, array $attrs = array(), $ellipsis = false )
    {
        echo self::_createButton(
            'reset',
            $id,
            'btn-default',
            $extra_class,
            $attrs,
            $caption !== null ? $caption : __( 'Reset', 'bookly' ),
            '{caption}' . ( $ellipsis ? '…' : '' )
        );
    }

    /**
     * Render cancel button.
     *
     * @param array  $attrs
     * @param bool $ellipsis
     */
    public static function renderCancel( $caption = null, array $attrs = array(), $ellipsis = false )
    {
        $attrs += array( 'data-dismiss' => 'bookly-modal' );
        echo self::_createButton(
            'button',
            null,
            'btn-default',
            '',
            $attrs,
            $caption ?: __( 'Cancel' ),
            '{caption}' . ( $ellipsis ? '…' : '' )
        );
    }

    /**
     * Render submit button.
     *
     * @param string $id
     * @param string $extra_class
     * @param string $caption
     * @param array  $attrs
     * @param bool $ellipsis
     */
    public static function renderSubmit( $id = 'bookly-save', $extra_class = null, $caption = null, array $attrs = array(), $ellipsis = false )
    {
        echo self::_createButton(
            'submit',
            $id,
            'btn-success',
            $extra_class,
            $attrs,
            $caption !== null ? $caption : __( 'Save', 'bookly' ),
            '{caption}' . ( $ellipsis ? '…' : '' )
        );
    }

    /**
     * Create button.
     *
     * @param string $type
     * @param string $id
     * @param string $class
     * @param string $extra_class
     * @param array  $attrs
     * @param string $caption
     * @param string $caption_template
     * @return string
     */
    private static function _createButton( $type, $id, $class, $extra_class, array $attrs, $caption, $caption_template )
    {
        $attrs['id']    = $id;
        $attrs['class'] = implode( ' ', array_filter( array( 'btn ladda-button', $class, $extra_class ) ) );
        $attrs['data-spinner-size'] = '40';
        $attrs['data-style'] = 'zoom-in';

        $attrs_str = '';
        foreach ( $attrs as $attr => $value ) {
            if ( $value !== null ) {
                $attrs_str .= sprintf( ' %s="%s"', $attr, esc_attr( $value ) );
            }
        }

        $caption = strtr( $caption_template, array( '{caption}' => esc_html( $caption ) ) );

        return strtr(
            '<button type="{type}"{attributes}><span class="ladda-label">{caption}</span></button>',
            array(
                '{type}'       => $type,
                '{attributes}' => $attrs_str,
                '{caption}'    => $caption,
            )
        );
    }
}