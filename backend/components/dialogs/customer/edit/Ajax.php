<?php
namespace Bookly\Backend\Components\Dialogs\Customer\Edit;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\Customer\Edit
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritdoc
     */
    protected static function permissions()
    {
        return array( 'saveCustomer' => array( 'staff', 'supervisor' ) );
    }

    /**
     * Create or edit a customer.
     */
    public static function saveCustomer()
    {
        $response = array();

        $params = self::postParameters();
        $errors = array();

        // Check for errors.
        if ( get_option( 'bookly_cst_first_last_name' ) ) {
            if ( $params['first_name'] == '' ) {
                $errors['first_name'] = array( 'required' );
            }
            if ( $params['last_name'] == '' ) {
                $errors['last_name'] = array( 'required' );
            }
        } else if ( $params['full_name'] == '' ) {
            $errors['full_name'] = array( 'required' );
        }

        if ( empty ( $errors ) ) {
            if ( ! $params['wp_user_id'] ) {
                $params['wp_user_id'] = null;
            }
            if ( ! $params['birthday'] ) {
                $params['birthday'] = null;
            }
            if ( ! $params['group_id'] ) {
                $params['group_id'] = null;
            }
            $params = Proxy\CustomerInformation::prepareCustomerFormData( $params );
            if ( isset( $params['info_fields'] ) ) {
                $params['info_fields'] = json_encode( $params['info_fields'] );
            }
            $form = new Forms\Customer();
            $form->bind( $params );
            /** @var Lib\Entities\Customer $customer */
            $customer = $form->save();
            $response['success']  = true;
            $response['customer'] = array(
                'id'          => $customer->getId(),
                'wp_user_id'  => $customer->getWpUserId(),
                'group_id'    => $customer->getGroupId(),
                'full_name'   => $customer->getFullName(),
                'first_name'  => $customer->getFirstName(),
                'last_name'   => $customer->getLastName(),
                'phone'       => $customer->getPhone(),
                'email'       => $customer->getEmail(),
                'notes'       => $customer->getNotes(),
                'birthday'    => $customer->getBirthday(),
                'info_fields' => json_decode( $customer->getInfoFields() ),
            );
        } else {
            $response['success'] = false;
            $response['errors']  = $errors;
        }

        wp_send_json( $response );
    }
}