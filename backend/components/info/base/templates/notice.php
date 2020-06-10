<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<div id="bookly-tbs" class="wrap">
    <div id="<?php echo $id ?>" class="alert alert-success alert-dismissible" role="alert" <?php if ( $hidden ) : ?>style="display: none" <?php endif ?>>
        <div class="d-md-flex form-row align-items-center">
            <img src="<?php echo plugins_url( 'bookly-responsive-appointment-booking-tool/backend/components/info/base/images/photo.png' ) ?>" alt="avatar" width="72" height="72" style="width: 72px; height: 72px;">
            <div class="pl-md-4 col" style="min-width: 50%">
                <div><b class="bookly-js-alert-title"><?php echo esc_html( $title ) ?></b> <?php echo $sub_title ?></div>
                <div class="font-weight-bold mt-2 w-100" style="line-height: 1;"><?php echo $message ?></div>
                <small class="text-muted">Daniel Williams, PO at Bookly</small>
            </div>
            <div class="d-lg-inline-flex d-md-block mr-n5 mr-md-0">
                <?php foreach ( $buttons as $button ) : ?>
                    <?php Buttons::render( null, $button['class'] . ' ml-md-1 mr-1 mb-1', $button['caption'] ) ?>
                <?php endforeach ?>
            </div>
        </div>
        <button type="button" class="close <?php echo esc_attr( $dismiss_js_class ) ?>" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>