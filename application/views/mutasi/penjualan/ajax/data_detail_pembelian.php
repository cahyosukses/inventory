<?php
$x = 0;
if ($data_barang->count() > 0) {
    foreach ($data_barang as $row) {
        $x++;
        ?>
        <tr>
            <td><?php echo $x ?></td>
            <td><?php echo $row->kode_brg; ?></td>
            <td><?php echo get_data_barang($row->kode_brg, 'nama_brg'); ?></td>
            <td><?php echo $row->jumlah; ?></td>
            <td><?php echo $row->satuan; ?></td>
            <td align="right"><?php echo rupiah($row->harga_beli); ?></td>
            <td><?php echo $row->diskon; ?></td>
            <td><?php echo $row->exp_date; ?></td>
        </tr>
    <?php } ?>

<?php } else { ?>
    <tr>
        <td colspan="8"><center>[ Daftar Barang Kosong ]</center></td>
    </tr>
<?php } ?>
