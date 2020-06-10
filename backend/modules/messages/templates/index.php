<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Support;
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Messages', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <table id="bookly-messages-list" class="table table-striped w-100">
                <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'bookly' ) ?></th>
                    <th><?php esc_html_e( 'Subject', 'bookly' ) ?></th>
                    <th><?php esc_html_e( 'Message', 'bookly' ) ?></th>
                </tr>
                </thead>
            </table>
        </div>
    </div>
</div>