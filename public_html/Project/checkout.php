<?php
require(__DIR__ . "/../../partials/nav.php");
if(!is_logged_in()){
    flash("You must log in to checkout.");
    die(header("Location: login.php"));
}
$user_id = $_SESSION["user"]["id"];
$results = [];
$db = getDB();
$stmt = $db->prepare("SELECT unit_cost, desired_quantity from CartItems WHERE user_id = :user_id");
try {
    $stmt->execute([":user_id" => $_SESSION["user"]["id"]]);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
$grand_sum = 0;
foreach($results as $index => $record){
    $grand_sum += $record["desired_quantity"] * $record["unit_cost"];
}

if (isset($_POST['submit'])) {
    $address_string = $_POST['fname'] . ', ' . $_POST['lname'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ', ' . $_POST['country'] . ', ' . $_POST['zip'] . ', ' . $_POST['apart'] . ', ' . $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $stmt = $db->prepare("INSERT INTO Orders (user_id, total_price, payment_method, address) VALUES(:user_id, :total_price, :payment_method, :address)");
    try {
        $stmt->execute([":user_id" => $user_id, ":total_price" => $grand_sum, ":payment_method" => $payment_method, ":address" => $address_string]);
        flash("Added to cart");
    } catch (Exception $e) {
        flash("<pre>" . var_export($e, true) . "</pre>");
    } 
}
?>
<style>
    .container-fluid{
        position: fixed;
        left: 50%;
        margin-left: -150px;
        border: 1px solid black;
        box-shadow: 5px 5px black;
        padding: 10px;
        background-color: #a2eda1;
        width: 300px;
        height: 300px;
    }

</style>
<div class="container-fluid">
    <h1><?php echo($grand_sum)?></h1>
    <form method="POST">
        <div>
            <label>Address Information</label>
            <input type="text" placeholder="First name" name="fname" required/>
            <input type="text" placeholder="Last name" name="lname" required/>
            <input type="text" placeholder="City" name="city"required />
            <input type="text" placeholder="State/Province" name="state"required/>
            <input type="text" placeholder="Country" name="country" required/>
            <input type="text" placeholder="Zip/Postal code" name="zip" required/>
            <input type="text" placeholder="Apartment, suite, etc" name="apart" required/>
            <input type="text" placeholder="Street address" name="address" required/>
        </div>
        <div>
            <label>Payment Method</label>
            <select name="payment_method" value="Visa">
                <option value="Visa">Visa</option>
                <option value="Master Card">Master card</option>
                <option value="American Express">American Express</option>
                <option value="Discover">Discover</option>
                <option value="Amex">Amex</option>
                <option value="Cash">Cash</option>
            </select>
        </div>
        <div>
        <input type="submit" value="Purchase" name="submit" />
        </div>
    </form>

</div>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>