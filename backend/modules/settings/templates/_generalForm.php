<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Backend\Modules\Settings\Proxy;
?>
<form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'general' ) ) ?>">
    <div class="card-body">
    <?php
        Selects::renderSingle( 'bookly_gen_time_slot_length', __( 'Time slot length', 'bookly' ), __( 'Select a time interval which will be used as a step when building all time slots in the system.', 'bookly' ), $values['bookly_gen_time_slot_length'] );
        Selects::renderSingle( 'bookly_gen_service_duration_as_slot_length', __( 'Set slot length as service duration', 'bookly' ), __( 'Enable this option to make slot length equal to service duration at the Time step of booking form.', 'bookly' ) );
        Selects::renderSingle( 'bookly_gen_default_appointment_status', __( 'Default appointment status', 'bookly' ), __( 'Select status for newly booked appointments.', 'bookly' ), array( array( CustomerAppointment::STATUS_PENDING, __( 'Pending', 'bookly' ) ), array( CustomerAppointment::STATUS_APPROVED, __( 'Approved', 'bookly' ) ), ) );
        Proxy\Pro::renderMinimumTimeRequirement();
        Inputs::renderNumber( 'bookly_gen_max_days_for_booking', __( 'Number of days available for booking', 'bookly' ), __( 'Set how far in the future the clients can book appointments.', 'bookly' ), 1, 1 );
        Selects::renderSingle( 'bookly_gen_use_client_time_zone', __( 'Display available time slots in client\'s time zone', 'bookly' ), __( 'The value is taken from client\'s browser.', 'bookly' ) );
        Selects::renderSingle( 'bookly_gen_allow_staff_edit_profile', __( 'Allow staff members to edit their profiles', 'bookly' ), __( 'If this option is enabled then all staff members who are associated with WordPress users will be able to edit their own profiles, services, schedule and days off.', 'bookly' ) );
        Selects::renderSingle( 'bookly_gen_link_assets_method', __( 'Method to include Bookly JavaScript and CSS files on the page', 'bookly' ), __( 'With "Enqueue" method the JavaScript and CSS files of Bookly will be included on all pages of your website. This method should work with all themes. With "Print" method the files will be included only on the pages which contain Bookly booking form. This method may not work with all themes.', 'bookly' ), array( array( 'enqueue', 'Enqueue' ), array( 'print', 'Print' ) ) );
        Selects::renderSingle( 'bookly_gen_collect_stats', __( 'Help us improve Bookly by sending anonymous usage stats', 'bookly' ) );
        Selects::renderSingle( 'bookly_app_show_powered_by', __( 'Powered by Bookly' ), __( 'Allow the plugin to set a Powered by Bookly notice on the booking widget to spread information about the plugin. This will allow the team to improve the product and enhance its functionality', 'bookly' ) );
        Selects::renderSingle( 'bookly_app_prevent_caching', __( 'Caching of pages with booking form', 'bookly' ), __( 'Select "Prevent" if you want Bookly to prevent caching by third-party caching plugins by adding a DONOTCACHEPAGE constant on pages with booking form.', 'bookly' ), array( array( 0, __( 'Allow', 'bookly' ) ), array( 1, __( 'Prevent', 'bookly' ) ), ) );
    ?>
    </div>
    <div class="card-footer bg-transparent d-flex justify-content-end">
        <?php ControlsInputs::renderCsrf() ?>
        <?php Buttons::renderSubmit() ?>
        <?php Buttons::renderReset( null, 'ml-2' ) ?>
    </div>
</form>