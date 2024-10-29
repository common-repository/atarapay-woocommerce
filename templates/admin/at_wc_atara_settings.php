<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('At_WC_Providers_List_Table')) {
    include_once AT_WC_ATARA_PATH . 'includes/admin/class-at-wc-atara-providers-table.php';
}

if (isset($_GET['updated'])) {
    add_settings_error('at_wc_atara_messages', 'at_wc_atara', __("Service provider created", 'atarapay-woo'), 'updated');
} else if (isset($_GET['error'])) {
    add_settings_error('at_wc_atara_messages', 'at_wc_atara', esc_html__($_GET['error'], 'atarapay-woo'));
}


$At_WC_Atara_Service_Provider = new At_WC_Atara_Service_Provider();

$nonce = wp_create_nonce("at_wc_atara_service_provider");
$banks = $At_WC_Atara_Service_Provider->get_banks();
?>

<div class="wrap at-wc-atara-settings">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/css/intlTelInput.css">
<style>
    tr.account-wise li {
        list-style-type: initial;
    }

    tr.account-wise ul{
        margin-left: 5%;
    }

</style>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/js/intlTelInput.min.js"></script>

<script type="text/javascript">
    var $ = jQuery;
            $(document).ready(function(){

                const input = document.querySelector("#phone");
                window.intlTelInput(input, {
                    initialCountry:"ng",
                    utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@19.2.16/build/js/utils.js",
                
                });
               
                $('select[name="type"]').on('change', function(){
                    $('tr.account-wise').hide();
                    $('tr.no-wise').show();
                    $('select[name="bank_name"]').prop('required', true );
                    $('input[name="bank_account_number"]').prop('required', true );
                    $('input[name="bank_account_name"]').prop('required', true );
                    var value = $(this).val();
                    if ( value == "2" ) {
                        $('tr.account-wise').show();
                        $('tr.no-wise').hide();
                        $('select[name="bank_name"]').prop('required', false );
                        $('input[name="bank_account_number"]').prop('required', false );
                        $('input[name="bank_account_name"]').prop('required', false );
                        
                    }
                });
                $('select[name="type"]').trigger('change');

                $("form.atsp").on('submit', function(e){
                    e.preventDefault();
                    if ( $('select[name="type"]').val() == 2 && $('input[name="account_id"]').val() == "" ) {
                        alert('Please provide your Wise account id.');
                        return false;
                    }

                    if ( $('select[name="type"]').val() == 2 &&  ! $('input[name="validate"]').is(':checked') ) {
                        alert('Please agree to the terms before adding a USD service provider.');
                        return false;
                    } 
                    
                    $(this).off('submit').submit();
                   
                });
            });
        </script>
    <h2><?php esc_html_e('AtaraPay', 'at_wc_atara'); ?></h2>

    <?php settings_errors('at_wc_atara_messages'); ?>

    <form action='admin-post.php' method="post" class="atsp">

        <h3>Create Service Provider</h3>
        <p style="color: #800000"><strong>Note: </strong>If you registered as an AtaraPay Marketplace Operator, ensure the phone numbers of service providers are only those of registered AtaraPay sellers that are associated with your account.</p>
        <table class="form-table">
            <tbody>
            <tr valign="top">
                    <th scope="row">Payout Currency</th>
                    <td>
                        <select name="type">
                            <option value="1"> <?php esc_html_e('NGN', 'atarapay-woo'); ?> </option>
                            <option value="2"> <?php esc_html_e('USD', 'atarapay-woo'); ?> </option>
                        
                        </select>
                    </td>
                </tr>


                <tr valign="top" class="no-wise">
                    <th scope="row">Bank Name</th>
                    <td>
                        <select required name="bank_name">
                            <option value=""> <?php esc_html_e('Select Bank...', 'atarapay-woo'); ?> </option>
                            <?php
                            foreach ($banks as $bank) {
                                $name = $bank['name'];
                                $bank = json_encode($bank);
                                echo "<option value='{$bank}'>{$name}</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr valign="top"  class="no-wise">
                    <th scope="row">Bank Account Name</th>
                    <td><input type="text" name="bank_account_name" class="input-field" required value="<?php echo (isset($_GET['bank_account_name']) ? $_GET['bank_account_name'] : '') ?>" /></td>
                </tr>
                

                <tr valign="top"  class="no-wise">
                    <th scope="row">Bank Account Number</th>
                    <td><input type="text" name="bank_account_number" class="input-field" required value="<?php echo (isset($_GET['bank_account_number']) ? $_GET['bank_account_number'] : '') ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Email Address</th>
                    <td><input type="email" name="email" class="input-field" required value="<?php echo (isset($_GET['email']) ? $_GET['email'] : '') ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Phone Number<br/><small>Enter the international code before the number. For example, +2348031234567.</small></th>
                    <td><input id="phone" type="text" name="phone" class="input-field" required value="<?php echo (isset($_GET['phone']) ? $_GET['phone'] : '') ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">First Name</th>
                    <td><input type="text" name="firstname" class="input-field" required value="<?php echo (isset($_GET['firstname']) ? $_GET['firstname'] : '') ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Last Name</th>
                    <td><input type="text" name="lastname" class="input-field" required value="<?php echo (isset($_GET['lastname']) ? $_GET['lastname'] : '') ?>" /></td>
                </tr>

                


                <tr valign="top" class="account-wise">
                    <th scope="row">Wise Account ID</th>
                    <td><input type="text" name="account_id" class="input-field" value="<?php echo (isset($_GET['firstname']) ? $_GET['firstname'] : '') ?>" /></td>
                </tr>

                <tr valign="top" class="account-wise">
                    <td style="width: 100%;">
                    <input type="checkbox" name="validate" />
                   <strong> I’ve read and agree to all the terms as stated below:</strong>
                   <ul>
                            <li>
                            You will bear the Wise transfer fees.
                            </li>
                            <li>
                            We would payout the USD value computed by our platform to your default ID.
                            </li>
                            <li>
                            You may then choose to withdraw from your Wise account to your local currency.
                            Your website transactions using AtaraPay 
                            API or plug-in must be in any non-NGN currency 
                            like GBP, USD, etc.
                            </li>
                   
                            <li>
                            It is your responsibility to communicate 
                            the terms above to your service provider 
                            (if they choose to receive payout in USD).
                            </li>
                            
                   </ul>
                
                
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button("Create Service Provider"); ?>
        <input type="hidden" name="action" value="at_wc_atara_save_service_provider">
        <input type="hidden" name="_nonce" value="<?php echo $nonce; ?>" />
        <input type="hidden" name="_wp_http_referer" value="/wp-admin/admin.php?page=atarapay">
    </form>
</div>


<div class="wrap">
    <h2><?php esc_html_e('Service Providers', 'at_wc_atara'); ?></h2>
    <form id="form-list" method="post">
        <input type="hidden" name="page" value="at_wc_atara" />
        <?php
        $list_table = new At_WC_Providers_List_Table();
        $list_table->prepare_items();
        $list_table->display();
        ?>
    </form>
</div>
