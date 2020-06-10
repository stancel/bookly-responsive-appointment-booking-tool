<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-collapse">
    <a class="h5" href="#<?php echo $id ?>" data-toggle="collapse" role="button" aria-expanded="<?php echo (string) $opened ?>"><?php echo esc_html( $title ) ?></a>
    <div id="<?php echo $id ?>" class="collapse<?php if ( $opened ) : ?> show<?php endif ?>">