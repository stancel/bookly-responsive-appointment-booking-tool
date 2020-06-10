<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Components\Support;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Config;

/** @var array $datatables */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Email notifications', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search notifications', 'bookly' ) ?>"/>
                    </div>
                </div>
                <div class="col-md-8 form-row justify-content-end pr-0">
                    <div class="col-auto">
                        <?php Buttons::renderDefault( 'bookly-js-settings', null, __( 'General settings', 'bookly' ), array(), true ) ?>
                    </div>
                    <?php Dialogs\Notifications\Dialog::renderNewNotificationButton() ?>
                    <?php Dialogs\TableSettings\Dialog::renderButton( 'email_notifications', 'BooklyL10n' ) ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <table id="bookly-js-notification-list" class="table table-striped w-100">
                        <thead>
                        <tr>
                            <?php foreach ( $datatables['email_notifications']['settings']['columns'] as $column => $show ) : ?>
                                <?php if ( $show ) : ?>
                                    <?php if ( $column == 'type' ) : ?>
                                        <th width="1"></th>
                                    <?php else : ?>
                                        <th><?php echo $datatables['email_notifications']['titles'][ $column ] ?></th>
                                    <?php endif ?>
                                <?php endif ?>
                            <?php endforeach ?>
                            <th width="75"></th>
                            <th width="16"><?php Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-all' ) ) ?></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="form-row mb-3">
                <div class="col-auto">
                    <?php Inputs::renderCsrf() ?>
                    <?php Buttons::renderDefault( 'bookly-js-test-email-notifications', null, __( 'Test email notifications', 'bookly' ), array(), true ) ?>
                </div>
                <div class="ml-auto mr-1">
                    <?php Buttons::renderDelete( 'bookly-js-delete-notifications' ) ?>
                </div>
            </div>
            <?php if ( Config::proActive() ) : ?>
                <div class="alert alert-info">
                    <div class="row">
                        <div class="col-md-12">
                            <?php if ( is_multisite() ) : ?>
                                <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), Common::prepareUrlReferrers( 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', 'cron_setup' ), network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
                            <?php else : ?>
                                <p><?php esc_html_e( 'To send scheduled notifications please execute the following command hourly with your cron:', 'bookly' ) ?></p>
                                <code>wget -q -O - <?php echo site_url( 'wp-cron.php' ) ?></code>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <?php $self::renderTemplate( '_test_email_modal' ) ?>
    <?php $self::renderTemplate( '_general_settings_modal' ) ?>
    <?php Dialogs\Notifications\Dialog::render() ?>
    <?php Dialogs\TableSettings\Dialog::render() ?>
</div>