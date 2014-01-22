<?php
include 'interkassa.php';
Interkassa::register();

$shop_id = '52e0105ebf4efc070d704c1d';
$secret_key = 'rVCrCFMt3Tdz2kzT';

// Create a shop
$shop = Interkassa_Shop::factory(array(
    'id' => $shop_id,
    'secret_key' => $secret_key
));

if (count($_POST))
{
    try
    {
        $status = $shop->receiveStatus($_POST); // POST is used by default
    } catch (Interkassa_Exception $e)
    {
        // The signature was incorrect, send a 400 error to interkassa
        // They should resend payment status request until they receive a 200 status
        header('HTTP/1.0 400 Bad Request');
        ob_start();
        var_dump($e);
        $output = ob_get_contents();
        file_put_contents('LOG.txt', $output);
        exit;
    }
    ob_start();
    $payment = $status->getPayment();

    var_dump($payment);
    $output = ob_get_contents();
    file_put_contents('LOG.txt', $output);
}
else
{
    // Create a payment
    $payment_id = '1'; // Your payment id
    $payment_amount = '12.52'; // The amount to charge your shop's user
    $payment_desc = 'Test'; // Payment description

    $payment = $shop->createPayment(array(
        'id' => $payment_id,
        'amount' => $payment_amount,
        'description' => $payment_desc,
        'locale' => 'en'
    ));
    $payment->setBaggage('test_baggage');

    ?>
    <form action="<?php echo $payment->getFormAction(); ?>" method="post">
        <?php foreach ($payment->getFormValues() as $field => $value): ?>
            <input type="hidden" name="<?php echo $field; ?>" value="<?php echo $value; ?>"/>
        <?php endforeach; ?>
        <button type="submit">Submit</button>
    </form>
<?php
}