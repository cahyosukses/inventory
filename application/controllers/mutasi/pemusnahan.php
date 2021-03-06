<?php

class Pemusnahan extends CI_Controller {

    private $m, $user_id;

    function __construct() {
        parent::__construct();
        $this->external->redirect_logged_in();
        $this->user_id = $this->external->get_session_name('user_id');
    }

    function tambah() {
        $mp = new MPemasok();
        $mb = new MBarang();

        $kode_psk = $mp->list_drop();
        $kode_brg = $mb->list_drop();

        $option_cb = array(
            'tunai' => 'Tunai',
            'kredit' => 'Kredit'
        );
        $option_st = array(
            'karton' => 'Karton',
            'dosin' => 'Dos',
            'biji' => 'Biji'
        );
        $data['form_action'] = site_url('mutasi/pembelian/proses');
        $data['no_bukti'] = array('name' => 'no_bukti', 'class' => 'span2', 'id' => 'no_bukti');
        $data['tgl_bukti'] = array('name' => 'tgl_bukti', 'class' => 'input-small', 'id' => 'tgl_bukti', 'value' => date('Y-m-d'));
        $data['keterangan'] = array('name' => 'keterangan', 'class' => 'input-block-level', 'rows' => '2', 'id'=>'keterangan');
        $data['jatuh_tempo'] = array('name' => 'jatuh_tempo', 'class' => 'input-small', 'id' => 'jatuh_tempo');
        $data['harga'] = array('name' => 'harga', 'class' => 'input-block-level', 'id' => 'harga');
        $data['jumlah'] = array('name' => 'jumlah', 'class' => 'input-mini', 'id' => 'jumlah');
        $data['diskon'] = array('name' => 'diskon', 'class' => 'input-mini', 'value' => '0', 'id'=>'diskon');
        $data['stl_disc'] = array('name' => 'stl_disc', 'class' => 'span2', 'style' => 'width: 199px;');
        $data['exp_date'] = array('name' => 'exp_date', 'class' => 'input-small', 'id' => 'exp_date');
        $data['kode_psk'] = form_dropdown('kode_prd', $kode_psk, '', 'id="kode_psk" class="chosen-select input-block-level"');
        $data['kode_brg'] = form_dropdown('kode_brg', $kode_brg, '', 'id="kode_brg" class="chosen-select input-block-level"');
        $data['cara_bayar'] = form_dropdown('cara_bayar', $option_cb, '', 'id="cara_bayar" class="input-small"');
        $data['satuan'] = form_dropdown('satuan', $option_st, '', 'id="satuan" class="span2" style="width: 213px;"');
        $this->load->view('mutasi/pemusnahan/tambah', $data);
    }

    function proses() {
        $ms = new MSementara();
        $mb = new MBarang();


        $data = array(
            'kode_brg' => $_POST['kode_brg'],
            'nama_brg' => $mb->get_record($_POST['kode_brg'], 'nama_brg'),
            'jumlah' => $_POST['jumlah'],
            'satuan' => $_POST['satuan'],
            'harga' => $_POST['harga'],
//            'no_urut' => $_POST['no_urut'],
            'exp_date' => $_POST['exp_date'],
            'diskon' => $_POST['diskon'],
            'user_id' => $this->user_id
        );
        $ms->form_insert($data);
//        redirect('mutasi/pembelian/tambah');
    }

    function load_data_barang() {
        sleep(3);
        $ms = new MSementara();
        $data['data_barang'] = $ms->where('user_id', $this->user_id)->get();
        $this->load->view('mutasi/pembelian/ajax/data_barang', $data);
    }

}

?>
