<?php
session_start();
require('./fpdf.php');

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Daftar item dan harga
$rental_items = array(
    "Kursi Futura" => 8000,
    "Kursi Futura + Sarung" => 13000,
    "Kursi Futura + Sarung + Pita" => 15000,
    "Kursi Tiffany" => 50000,
    "Meja Kotak Polos (75*100)" => 30000,
    "Meja Kotak Cover" => 55000,
    "Meja Bulat Polos (diameter 120)" => 50000,
    "Meja Bulat cover (diameter 120)" => 70000,
    "Meja Bulat Polos (diameter 160)" => 85000,
    "Meja Bulat cover (diameter 160)" => 165000,
    "Kipas Blower" => 350000,
    "Karpet Buana" => 10000,
    "Alas Papan + Karpet" => 40000,
    "Tenda Dekorasi VIP (Tertutup)" => 50000,
    "Tenda Dekorasi" => 40000,
    "Tenda Polos" => 25000,
    "Tenda Plafon Dekor" => 27500,
    "Tenda Roder Polos" => 95000,
    "Tenda Roder Dekor" => 120000,
    "Tenda Roder Transparan" => 150000,
    "Panggung T. 1 M" => 85000,
    "Panggung T. 40 Cm" => 45000,
    "Full Ridging (5x6)" => 2500000,
    "Ridging Gladakan (5x6)" => 1000000,
    "Lampu TL 45 Watt" => 40000,
    "Lampu Sorot" => 75000,
    "Beam" => 500000,
    "Lampu Hias" => 250000,
    "Parlet" => 190000,
    "Paket Lighting" => 1200000,
    "Sound System (Monitor) + 2 mic" => 750000,
    "Penambahan Mic" => 135000,
    "Wireless Sound Portable + 1 Mic" => 500000,
    "Paket Sound" => 2750000
);

// Fungsi untuk menambah atau memperbarui item di keranjang
function updateCart($item, $quantity) {
    global $rental_items;
    if (array_key_exists($item, $rental_items) && $quantity > 0) {
        $_SESSION['cart'][$item] = $quantity;
    } elseif (array_key_exists($item, $rental_items) && $quantity == 0) {
        unset($_SESSION['cart'][$item]);
    }
}

// Fungsi untuk membuat PDF
function generatePDF() {
    global $rental_items;
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'Simulasi Biaya Sewa',0,1,'C');
    $pdf->Ln(10);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(80,10,'Item',1);
    $pdf->Cell(30,10,'Jumlah',1);
    $pdf->Cell(40,10,'Harga Satuan',1);
    $pdf->Cell(40,10,'Total',1);
    $pdf->Ln();

    $pdf->SetFont('Arial','',12);
    $total_cost = 0;
    foreach ($_SESSION['cart'] as $item => $quantity) {
        $price = $rental_items[$item];
        $total = $price * $quantity;
        $total_cost += $total;

        $pdf->Cell(80,10,$item,1);
        $pdf->Cell(30,10,$quantity,1);
        $pdf->Cell(40,10,'Rp '.number_format($price, 0, ',', '.'),1);
        $pdf->Cell(40,10,'Rp '.number_format($total, 0, ',', '.'),1);
        $pdf->Ln();
    }

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(150,10,'Total Biaya:',1);
    $pdf->Cell(40,10,'Rp '.number_format($total_cost, 0, ',', '.'),1);

    $pdf->Output('D', 'Simulasi_Biaya_Sewa.pdf');
    exit;
}

// Proses permintaan AJAX dan PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_cart') {
        updateCart($_POST['item'], intval($_POST['quantity']));
        echo json_encode($_SESSION['cart']);
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'generate_pdf') {
        generatePDF();
    }
}

// Hitung total
$total_cost = 0;
foreach ($_SESSION['cart'] as $item => $quantity) {
    $total_cost += $rental_items[$item] * $quantity;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Biaya Sewa</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        form { margin-bottom: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .remove-item { color: red; cursor: pointer; }
        #export-pdf { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kalkulator Biaya Sewa</h1>
        
        <form id="add-item-form">
            <label for="item">Pilih Item:</label>
            <select name="item" id="item" required>
                <?php foreach ($rental_items as $item => $price): ?>
                    <option value="<?php echo htmlspecialchars($item); ?>"><?php echo htmlspecialchars($item) . " - Rp " . number_format($price, 0, ',', '.'); ?></option>
                <?php endforeach; ?>
            </select>
            
            <label for="quantity">Jumlah:</label>
            <input type="number" name="quantity" id="quantity" min="1" value="1" required>
            
            <button type="submit">Tambahkan Item</button>
        </form>

        <h2>Keranjang Sewa</h2>
        <table id="cart-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $item => $quantity): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item); ?></td>
                        <td><input type="number" class="item-quantity" data-item="<?php echo htmlspecialchars($item); ?>" value="<?php echo $quantity; ?>" min="0"></td>
                        <td>Rp <?php echo number_format($rental_items[$item], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($rental_items[$item] * $quantity, 0, ',', '.'); ?></td>
                        <td><span class="remove-item" data-item="<?php echo htmlspecialchars($item); ?>">Hapus</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <h2>Total Biaya: <span id="total-cost">Rp <?php echo number_format($total_cost, 0, ',', '.'); ?></span></h2>
        
        <button id="export-pdf">Ekspor ke PDF</button>
    </div>

    <script>
    $(document).ready(function() {
        function updateCartDisplay() {
            $.post('', { action: 'update_cart', item: 'dummy', quantity: 0 }, function(data) {
                var cart = JSON.parse(data);
                var total = 0;
                var tableHtml = '';
                
                for (var item in cart) {
                    var quantity = cart[item];
                    var price = parseFloat($('#item option[value="' + item + '"]').text().split('Rp ')[1].replace('.', '').replace(',', '.'));
                    var itemTotal = price * quantity;
                    total += itemTotal;
                    
                    tableHtml += '<tr>' +
                        '<td>' + item + '</td>' +
                        '<td><input type="number" class="item-quantity" data-item="' + item + '" value="' + quantity + '" min="0"></td>' +
                        '<td>Rp ' + price.toLocaleString('id-ID') + '</td>' +
                        '<td>Rp ' + itemTotal.toLocaleString('id-ID') + '</td>' +
                        '<td><span class="remove-item" data-item="' + item + '">Hapus</span></td>' +
                        '</tr>';
                }
                
                $('#cart-table tbody').html(tableHtml);
                $('#total-cost').text('Rp ' + total.toLocaleString('id-ID'));
            });
        }

        $('#add-item-form').submit(function(e) {
            e.preventDefault();
            var item = $('#item').val();
            var quantity = $('#quantity').val();
            
            $.post('', { action: 'update_cart', item: item, quantity: quantity }, function() {
                updateCartDisplay();
            });
        });

        $(document).on('change', '.item-quantity', function() {
            var item = $(this).data('item');
            var quantity = $(this).val();
            
            $.post('', { action: 'update_cart', item: item, quantity: quantity }, function() {
                updateCartDisplay();
            });
        });

        $(document).on('click', '.remove-item', function() {
            var item = $(this).data('item');
            
            $.post('', { action: 'update_cart', item: item, quantity: 0 }, function() {
                updateCartDisplay();
            });
        });

        $('#export-pdf').click(function() {
            $('<form action="' + window.location.href + '" method="post">' +
              '<input type="hidden" name="action" value="generate_pdf">' +
              '</form>').appendTo('body').submit().remove();
        });
    });
    </script>
</body>
</html>