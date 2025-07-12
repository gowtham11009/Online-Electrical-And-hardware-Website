<!-- Masthead-->
<header class="masthead">
    <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center text-center">
            <div class="col-lg-10 align-self-center mb-4 page-title">
                <h1 class="text-white">Shopping Cart</h1>
                <hr class="divider my-4 bg-dark" />
            </div>

        </div>
    </div>
</header>
<section class="page-section" id="menu">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="sticky">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8"><b>Items</b></div>
                                <div class="col-md-4 text-right"><b>Total</b></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                if (isset($_SESSION['login_user_id'])) {
                    $data = "where c.user_id = '" . $_SESSION['login_user_id'] . "' ";
                } else {
                    $ip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
                    $data = "where c.client_ip = '" . $ip . "' ";
                }
                $total = 0;
                $get = $conn->query("SELECT *,c.id as cid FROM cart c inner join product_list p on p.id = c.product_id " . $data);
                while ($row = $get->fetch_assoc()):
                    $total += ($row['qty'] * $row['price']);
                    ?>

                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 d-flex align-items-center" style="text-align: -webkit-center">
                                    <div class="col-auto">
                                        <a href="admin/ajax.php?action=delete_cart&id=<?php echo $row['cid'] ?>"
                                            class="rem_cart btn btn-sm btn-outline-danger"
                                            data-id="<?php echo $row['cid'] ?>"><i class="fa fa-trash"></i></a>
                                    </div>
                                    <div class="col-auto flex-shrink-1 flex-grow-1 text-center">
                                        <img src="assets/img/<?php echo $row['img_path'] ?>" alt="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <p><b>
                                            <large><?php echo $row['name'] ?></large>
                                        </b></p>
                                    <p class='truncate'> <b><small>Desc :<?php echo $row['description'] ?></small></b></p>
                                    <p> <b><small>Unit Price :<?php echo number_format($row['price'], 2) ?></small></b></p>
                                    <p><small>QTY :</small></p>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary qty-minus" type="button"
                                                data-id="<?php echo $row['cid'] ?>"><span class="fa fa-minus"></button>
                                        </div>
                                        <input type="number" readonly value="<?php echo $row['qty'] ?>" min=1
                                            class="form-control text-center" name="qty">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary qty-plus" type="button" id=""
                                                data-id="<?php echo $row['cid'] ?>"><span
                                                    class="fa fa-plus"></span></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <b>
                                        <large class="item-total" id="item-total-<?php echo $row['cid'] ?>">
                                            <?php echo number_format($row['qty'] * $row['price'], 2) ?>
                                        </large>
                                    </b>
                                    <span class="d-none unit-price"><?php echo number_format($row['price'], 2) ?></span>
                                    <!-- Hidden price for JS -->
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </div>
            <div class="col-md-4">
                <div class="sticky">
                    <div class="card">
                        <div class="card-body">
                            <p>
                                <large>Total Amount</large>
                            </p>
                            <hr>
                            <p class="text-right"><b id="grand-total"><?php echo number_format($total, 2) ?></b></p>
                            <hr>
                            <div class="text-center">
                                <button class="btn btn-block btn-outline-dark" type="button" id="checkout">Proceed to
                                    Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    .card p {
        margin: unset
    }

    .card img {
        max-width: calc(100%);
        max-height: calc(59%);
    }

    div.sticky {
        position: -webkit-sticky;
        /* Safari */
        position: sticky;
        top: 4.7em;
        z-index: 10;
        background: white
    }

    .rem_cart {
        position: absolute;
        left: 0;
    }
</style>
<script>

    $(document).ready(function () {
        function update_qty(qty, id, price) {
            if (isNaN(qty) || qty < 1) return; // Prevent invalid or negative quantity

            start_load();
            $.ajax({
                url: 'admin/ajax.php?action=update_cart_qty',
                method: "POST",
                data: { id: id, qty: qty },
                success: function (resp) {
                    if (resp == 1) {
                        let totalPrice = (qty * price).toFixed(2);
                        $("#item-total-" + id).text(totalPrice); // Update item total price
                        update_grand_total(); // Update grand total dynamically
                        end_load();
                    }
                }
            });
        }

        function update_grand_total() {
            let grandTotal = 0;
            $(".item-total").each(function () {
                let itemTotal = parseFloat($(this).text().replace(/,/g, '')); // Remove commas
                if (!isNaN(itemTotal)) {
                    grandTotal += itemTotal;
                }
            });
            $("#grand-total").text(grandTotal.toFixed(2)); // Update grand total price
        }

        $('.qty-minus').click(function () {
            let input = $(this).parent().siblings('input[name="qty"]');
            let qty = parseInt(input.val()) - 1;
            let id = $(this).attr('data-id');
            let price = parseFloat($(this).closest('.card').find('.unit-price').text().replace(/,/g, ''));

            if (qty >= 1) {
                input.val(qty);
                update_qty(qty, id, price);
            }
        });

        $('.qty-plus').click(function () {
            let input = $(this).parent().siblings('input[name="qty"]');
            let qty = parseInt(input.val()) + 1;
            let id = $(this).attr('data-id');
            let price = parseFloat($(this).closest('.card').find('.unit-price').text().replace(/,/g, ''));

            if (!isNaN(qty) && qty > 0) {
                input.val(qty);
                update_qty(qty, id, price);
            }
        });

        $('#checkout').click(function () {
    let isLoggedIn = <?php echo isset($_SESSION['login_user_id']) ? 'true' : 'false'; ?>;
    
    if (isLoggedIn) {
        location.replace("index.php?page=checkout");
    } else {
        uni_modal("Login Required", "login.php?page=checkout");
    }
});

    });
</script>