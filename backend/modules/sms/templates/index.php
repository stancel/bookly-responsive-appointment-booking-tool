<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Support;
use Bookly\Backend\Components\Dialogs;
/** @var Bookly\Lib\SMS $sms */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'SMS Notifications', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <?php if ( $is_logged_in ) : ?>
                <div class="form-row">
                    <div class="col-xs-6 col-sm-8 col-md-9">
                        <div class="form-row">
                            <div class="col-sm-7">
                                <h4><?php esc_html_e( 'Your balance', 'bookly' ) ?>: <b>$<?php echo $sms->getBalance() ?></b></h4>
                                <div class="bookly-js-checkboxes" style="padding-left: 14px">
                                    <img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ) ?>" style="display: none; position: absolute; margin-top: 4px;">
                                    <?php Inputs::renderCheckBox( __( 'Send email notification to administrators at low balance', 'bookly' ), null, get_option( 'bookly_sms_notify_low_balance' ), array( 'name' => 'bookly_sms_notify_low_balance' ) ) ?>
                                    <img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/resources/images/loading.gif' ) ?>" style="display: none; position: absolute; margin-top: 4px;">
                                    <?php Inputs::renderCheckBox( __( 'Send weekly summary to administrators', 'bookly' ), null, get_option( 'bookly_sms_notify_weekly_summary' ), array( 'name' => 'bookly_sms_notify_weekly_summary' ) ) ?>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <h5><?php esc_html_e( 'Sender ID', 'bookly' ) ?>: <b class="bookly-js-sender-id"><?php echo $sms->getSenderId() ?></b> <small><a id="bookly-open-tab-sender-id" href="#"><?php esc_html_e( 'Change', 'bookly' ) ?></a></small>
                                    <?php if ( $sms->getSenderIdApprovalDate() ) : ?>
                                        <span class="text-muted text-form bookly-js-sender-id-approval-date"><?php esc_html_e( 'Approved at', 'bookly' ) ?>: <strong><?php echo \Bookly\Lib\Utils\DateTime::formatDate( $sms->getSenderIdApprovalDate() ) ?></strong></span>
                                    <?php endif ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-sm-4 col-md-3 text-right">
                        <form method="post" class="btn-group">
                            <button type="button" class="btn btn-success" data-toggle="bookly-modal" href="#modal_change_password">
                                <i class="far fa-fw fa-user"></i>
                                <?php echo $sms->getUserName() ?>
                            </button>
                            <button class="btn btn-default" type="submit" name="form-logout"><?php esc_html_e( 'Log out', 'bookly' ) ?></button>
                        </form>
                    </div>
                </div>

                <?php $self::renderTemplate( '_invoice', array( 'invoice' => $sms->getInvoiceData() ) ) ?>
                <ul class="nav nav-tabs mb-3" id="sms_tabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="bookly-tab" href="#notifications"><?php esc_html_e( 'Notifications', 'bookly' ) ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#add_money"><?php esc_html_e( 'Add money', 'bookly' ) ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#auto_recharge"><?php esc_html_e( 'Auto-Recharge', 'bookly' ) ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#purchases"><?php esc_html_e( 'Purchases', 'bookly' ) ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#sms_details"><?php esc_html_e( 'SMS Details', 'bookly' );
                        if ( $undelivered_count ) : ?> <span class="badge bg-danger"><?php echo $undelivered_count ?></span><?php endif ?></a> </li>
                    <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#price_list"><?php esc_html_e( 'Price list', 'bookly' ) ?></a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="bookly-tab" href="#sender_id"><?php esc_html_e( 'Sender ID', 'bookly' ) ?></a></li>
                </ul>
            <?php endif ?>

            <?php if ( $is_logged_in ) : ?>
                <div class="tab-content mt-3">
                    <div class="tab-pane active" id="notifications"><?php include '_notifications.php' ?></div>
                    <div class="tab-pane" id="add_money"><?php include '_buttons.php' ?></div>
                    <div class="tab-pane" id="auto_recharge"><?php include '_auto_recharge.php' ?></div>
                    <div class="tab-pane" id="purchases"><?php include '_purchases.php' ?></div>
                    <div class="tab-pane" id="sms_details"><?php include '_sms_details.php' ?></div>
                    <div class="tab-pane" id="price_list"><?php include '_price.php' ?></div>
                    <div class="tab-pane" id="sender_id"><?php include '_sender_id.php' ?></div>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    <p><?php esc_html_e( 'SMS Notifications (or "Bookly SMS") is a service for notifying your customers via text messages which are sent to mobile phones.', 'bookly' ) ?></p>
                    <p><?php esc_html_e( 'It is necessary to register in order to start using this service.', 'bookly' ) ?></p>
                    <p><?php esc_html_e( 'After registration you will need to configure notification messages and top up your balance in order to start sending SMS.', 'bookly' ) ?></p>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body bookly-js-confirm-form"<?php if ( $show_form !== 'confirm' ) : ?> style="display: none;"<?php endif ?>>
                                <legend><?php esc_html_e( 'Thank you for registration.', 'bookly' ) ?></legend>
                                <p><?php esc_html_e( 'You\'re almost ready to get started with Bookly SMS Service.', 'bookly' ) ?></p>
                                <p><?php esc_html_e( 'An email containing the confirmation code has been sent to your email address.', 'bookly' ) ?></p>
                                <p>
                                    <?php esc_html_e( 'To complete registration, please enter the confirmation code below.', 'bookly' ) ?>
                                </p>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control bookly-js-confirmation-code" placeholder="<?php esc_attr_e( 'Confirmation code', 'bookly') ?>" aria-label="Recipient's username" aria-describedby="basic-addon2">
                                    <div class="input-group-append">
                                        <?php Buttons::render( 'bookly-js-confirm-sms-account', 'btn-success', __( 'Confirm' ) ); ?>
                                    </div>
                                </div>
                                <hr>
                                <h6>
                                    <b><?php esc_html_e( 'Didn\'t receive the email?', 'bookly' ) ?></b>
                                </h6>
                                <ol>
                                    <li>
                                        <?php printf( esc_html__( 'Is %s the correct email?', 'bookly' ), sprintf( '<a href="mailto:%1$s">%1$s</a>', get_option( 'bookly_sms_unverified_username' ) ) ) ?>
                                        <br><?php printf( esc_html__( 'If not, you can %s restart the registration process%s.', 'bookly' ), '<a href="#" class="bookly-js-show-register-form">', '</a>' ) ?>
                                    </li>
                                    <li>
                                        <?php esc_html_e( 'Check your spam folder.', 'bookly' ) ?>
                                    </li>
                                    <li>
                                        <?php printf( esc_html__( 'Click %s here %s to resend the email.', 'bookly' ), '<a href="#" class="bookly-js-resend-confirmation">', '</a>' ) ?>
                                    </li>
                                </ol>
                            </div>

                            <form method="post" class="card-body bookly-js-login-form" action="<?php echo esc_url( remove_query_arg( array( 'paypal_result', 'auto-recharge', 'tab', ) ) ) ?>" style="display: none;">
                                <h5 class="cart-title"><?php esc_html_e( 'Login', 'bookly' ) ?></h5>
                                <div class="form-group">
                                    <label for="bookly-username"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                                    <input id="bookly-username" class="form-control" type="text" required="required" value="" name="username">
                                </div>
                                <div class="form-group">
                                    <label for="bookly-password"><?php esc_html_e( 'Password', 'bookly' ) ?></label>
                                    <input id="bookly-password" class="form-control" type="password" required="required" name="password">
                                </div>

                                <button type="submit" name="form-login" class="btn btn-success mb-2"><?php esc_html_e( 'Log In', 'bookly' ) ?></button>
                                <a href="#" class="bookly-js-show-register-form mx-2"><?php esc_html_e( 'Registration', 'bookly' ) ?></a><br>
                                <a href="#" class="bookly-js-show-forgot-form"><?php esc_html_e( 'Forgot password', 'bookly' ) ?></a>
                            </form>

                            <form method="post" class="card-body bookly-js-register-form"<?php if ( $show_form !== 'registration' ) : ?> style="display: none;"<?php endif ?>>
                                <h5 class="rard-title"><?php esc_html_e( 'Registration', 'bookly' ) ?></h5>
                                <div class="form-group">
                                    <label for="bookly-r-username"><?php esc_html_e( 'Email', 'bookly' ) ?></label>
                                    <input id="bookly-r-username" name="username" class="form-control" required="required" value="" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="bookly-r-password"><?php esc_html_e( 'Password', 'bookly' ) ?></label>
                                    <input id="bookly-r-password" name="password" class="form-control" required="required" value="" type="password">
                                </div>
                                <div class="form-group">
                                    <label for="bookly-r-repeat-password"><?php esc_html_e( 'Repeat password', 'bookly' ) ?></label>
                                    <input id="bookly-r-repeat-password" name="password_repeat" class="form-control" required="required" value="" type="password">
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="bookly-r-tos" name="accept_tos" required="required" value="1">
                                        <label class="custom-control-label" for="bookly-r-tos">
                                            <?php printf( __( 'I accept <a href="%1$s" target="_blank">Service Terms</a> and <a href="%2$s" target="_blank">Privacy Policy</a>', 'bookly' ), 'https://www.booking-wp-plugin.com/terms/', 'https://www.booking-wp-plugin.com/privacy/' ) ?>
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" name="form-registration" class="btn btn-success mr-2"><?php esc_html_e( 'Register', 'bookly' ) ?></button>
                                <a href="#" class="bookly-js-show-login-form"><?php esc_html_e( 'Log In', 'bookly' ) ?></a>
                            </form>

                            <form method="post" class="card-body bookly-js-forgot-form" style="display: none;">
                                <h5 class="card-title"><?php esc_html_e( 'Forgot password', 'bookly' ) ?></h5>
                                <div class="form-group">
                                    <input name="username" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Email', 'bookly' ) ?>"/>
                                </div>
                                <div class="form-group hidden">
                                    <input name="code" class="form-control" value="" type="text" placeholder="<?php esc_attr_e( 'Enter code from email', 'bookly' ) ?>"/>
                                </div>
                                <div class="form-group hidden">
                                    <input name="password" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>"/>
                                </div>
                                <div class="form-group hidden">
                                    <input name="password_repeat" class="form-control" value="" type="password" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>"/>
                                </div>
                                <div class="form-group">
                                    <button class="btn btn-success bookly-js-form-forgot-next mr-2" data-step="0"><?php esc_html_e( 'Next', 'bookly' ) ?></button>
                                    <a href="#" class="bookly-js-show-login-form"><?php esc_html_e( 'Log In', 'bookly' ) ?></a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <?php include '_price.php' ?>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <?php if ( $is_logged_in ) : ?>
        <div class="bookly-modal bookly-fade" id="modal_change_password" tabindex=-1 role="dialog">
            <form id="form-change-password">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php esc_html_e( 'Change password', 'bookly' ) ?></h5>
                            <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="old_password"><?php esc_html_e( 'Old password', 'bookly' ) ?></label>
                                <input type="password" class="form-control" id="old_password" name="old_password" placeholder="<?php esc_attr_e( 'Old password', 'bookly' ) ?>">
                            </div>
                            <div class="form-group">
                                <label for="new_password"><?php esc_html_e( 'New password', 'bookly' ) ?></label>
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="<?php esc_attr_e( 'New password', 'bookly' ) ?>">
                            </div>
                            <div class="form-group">
                                <label for="new_password_repeat"><?php esc_html_e( 'Repeat new password', 'bookly' ) ?></label>
                                <input type="password" class="form-control" id="new_password_repeat" placeholder="<?php esc_attr_e( 'Repeat new password', 'bookly' ) ?>">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <?php Inputs::renderCsrf() ?>
                            <?php Buttons::renderSubmit( 'ajax-send-change-password', 'btn-sm' ) ?>
                        </div>
                        <input type="hidden" name="action" value="bookly_change_password">
                    </div>
                </div>
            </form>
        </div>
    <?php endif ?>
<?php Dialogs\TableSettings\Dialog::render() ?>
</div>