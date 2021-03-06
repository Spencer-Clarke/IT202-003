<?php
require(__DIR__ . "/../../partials/nav.php");
if(!is_logged_in()){
    flash("You must log in to view cart.");
    die(header("Location: login.php"));
}
$results = [];
$db = getDB();
$stmt = $db->prepare("SELECT id, product_id, unit_cost, desired_quantity from CartItems WHERE user_id = :user_id");
try {
    $stmt->execute([":user_id" => $_SESSION["user"]["id"]]);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}

?>

<div class="container-fluid">
    <h1>Cart</h1>
    <?php
        $ids = [];
        $sum = 0;
        echo("<form method='POST' class='form'><br>");
        echo("<input type='submit' name='clear_all' value='Empty cart' class='delete_button'/>");
        foreach($results as $index => $record){
            echo("<div class='cart_item'>");
            foreach($record as $column => $value){
                if($column === 'id'){
                    $id = $value;
                    array_push($ids, $id);
                }
                else if($column === 'product_id'){
                    $product_id = $value;
                    $results_products = [];
                    $stmt = $db->prepare("SELECT name from Products WHERE id = :product_id");
                    try {
                        $stmt->execute([":product_id" => $value]);
                        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if ($r) {
                            $results_products = $r;
                            $name = $results_products[0]["name"];
                        }
                    } catch (PDOException $e) {
                        flash("<pre>" . var_export($e, true) . "</pre>");
                    }
                }
                else if($column === 'unit_cost'){
                    $cost = $value;
                }
                else if($column === 'desired_quantity'){
                    $quantity = $value;
                }
                else{
                    echo($value);
                }
            }
            $sum += $quantity*$cost;
            echo("Quantity: <input type='number' min='0' name='quantity". $id . "' value='" . $quantity . "'/><br>");
            echo("<input type='submit' value='Submit' name='submit" . $id . "' /><br>");
            echo("<input type='submit' value='Remove' name = 'remove" . $id . "' /><br>");
            echo("Name: " . $name . "<br>");
            echo("Unit price: " . $cost . "<br>");
            echo("Total cost: " . $cost*$quantity . "<br>");
            echo("<a href='item_info.php?id=" . $product_id . "'>Product info</a>");
            echo("</div><br>");
        }
        echo("Sum: " . $sum);
        echo("</form>")
    ?>
    <?php
        if(isset($_POST["clear_all"])){
            $stmt = $db->prepare("DELETE FROM CartItems WHERE user_id = :user_id");
            try {
                $stmt->execute([":user_id" => $_SESSION["user"]["id"]]);
                header("Refresh:0");
            } catch (Exception $e) {
                flash("<pre>" . var_export($e, true) . "</pre>");
            }
        }
        foreach($ids as $current_id){
            if(isset($_POST["remove" . $current_id])){
                $stmt = $db->prepare("DELETE FROM CartItems WHERE id= :id");
                try {
                    $stmt->execute([":id" => $current_id]);
                    header("Refresh:0");
                } catch (Exception $e) {
                    flash("<pre>" . var_export($e, true) . "</pre>");
                }
            }
            if(isset($_POST["submit" . $current_id])){
                $quantity_to_insert = se($_POST, "quantity" . $current_id, "", false);
                $sqlstr = "UPDATE CartItems SET desired_quantity= :desired_quantity WHERE id= :id";
                if($quantity_to_insert == 0){
                    $sqlstr = "DELETE FROM CartItems WHERE id= :id AND :desired_quantity=:desired_quantity";
                }
                $stmt = $db->prepare($sqlstr);
                try {
                    $stmt->execute([":desired_quantity" => $quantity_to_insert, ":id" => $current_id]);
                    header("Refresh:0");
                } catch (Exception $e) {
                    flash("<pre>" . var_export($e, true) . "</pre>");
                }
            }
        }
    ?>
</div>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>