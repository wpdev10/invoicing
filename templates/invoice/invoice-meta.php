<?php
/**
 * Displays the invoice meta data.
 *
 * This template can be overridden by copying it to yourtheme/invoicing/invoice/invoice-meta.php.
 *
 * @version 1.0.19
 */

defined( 'ABSPATH' ) || exit;

?>

        <?php do_action( 'getpaid_before_invoice_meta', $invoice ); ?>
        <div class="getpaid-invoice-meta-data">


            <?php do_action( 'getpaid_before_invoice_meta_table', $invoice ); ?>
            <table class="table table-bordered">
                <tbody>

                    <?php do_action( "getpaid_before_invoice_meta_rows", $invoice ); ?>
                    <?php foreach ( $meta as $key => $data ) : ?>

                        <?php if ( ! empty( $data['value'] ) ) : ?>

                            <?php do_action( "getpaid_before_invoice_meta_$key", $invoice, $data ); ?>

                            <tr class="getpaid-invoice-meta-<?php echo sanitize_html_class( $key ); ?>">

                                <th>
                                    <?php echo sanitize_text_field( $data['label'] ); ?>
                                </th>

                                <td>
                                    <?php echo wp_kses_post( $data['value'] ); ?>
                                </td>

                            </tr>

                            <?php do_action( "getpaid_after_invoice_meta_$key", $invoice, $data ); ?>

                        <?php endif; ?>
                    
                    <?php endforeach; ?>
                    <?php do_action( "getpaid_after_invoice_meta_rows", $invoice ); ?>

                </tbody>
            </table>
            <?php do_action( 'getpaid_after_invoice_meta_table', $invoice ); ?>


        </div>
        <?php do_action( 'getpaid_after_invoice_meta', $invoice ); ?>

<?php