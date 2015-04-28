<?php

class Pembelian extends CI_Controller {

    private $m, $user_id;

    function __construct() {
        parent::__construct();
        $this->external->redirect_logged_in();
        $this->user_id = $this->external->get_session_name('user_id');
        $this->m = new MPembelian();
    }

    function index($page = 1) {
        $data['data_pembelian'] = $this->m->get_paged($page, 10);
        $this->load->view('mutasi/pembelian/index', $data);
    }

    function tambah() {        
        $mp = new MPemasok();
        $mb = new MBarang();
        $ms = new MSementara();

        $data['data_barang'] = $ms->where('user_id', $this->user_id)->get();
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
        $data['no_bukti'] = array('name' => 'no_bukti', 'class' => 'span2', 'id' => 'no_bukti', 'required' => 'required');
        $data['tgl_bukti'] = array('name' => 'tgl_bukti', 'class' => 'input-small', 'id' => 'tgl_bukti', 'value' => date('Y-m-d'), 'required' => 'required');
        $data['keterangan'] = array('name' => 'keterangan', 'class' => 'input-block-level', 'rows' => '2', 'id' => 'keterangan');
        $data['jatuh_tempo'] = array('name' => 'jatuh_tempo', 'class' => 'input-small', 'id' => 'jatuh_tempo', 'value' => '0');
        $data['harga'] = array('name' => 'harga', 'class' => 'input-block-level', 'id' => 'harga', 'required' => 'required');
        $data['jumlah'] = array('name' => 'jumlah', 'class' => 'input-mini', 'id' => 'jumlah', 'required' => 'required');
        $data['diskon'] = array('name' => 'diskon', 'class' => 'input-mini', 'value' => '0', 'id' => 'diskon');
        $data['harga_stl_diskon'] = array('name' => 'harga_stl_diskon', 'class' => 'span2', 'style' => 'width: 199px;', 'id' => 'harga_stl_diskon', 'readonly' => 'readonly');
        $data['exp_date'] = array('name' => 'exp_date', 'class' => 'input-small', 'id' => 'exp_date');
        $data['kode_psk'] = form_dropdown('kode_psk', $kode_psk, '', 'id="kode_psk" class="chosen-select input-block-level" required="required"');
        $data['kode_brg'] = array('name' => 'kode_brg', 'class' => 'span2', 'placeholder' => 'Masukan nama barang...', 'id' => 'kode_brg', 'data-url' => site_url('master/barang/nama_barang_auto'));
        $data['nama_brg'] = array('name' => 'nama_brg', 'id' => 'nama_brg', 'class' => 'input-block-level', 'readonly' => 'readonly');
        $data['cara_bayar'] = form_dropdown('cara_bayar', $option_cb, '', 'id="cara_bayar" class="input-small" required="required"');
        $data['satuan'] = form_dropdown('satuan', $option_st, '', 'id="satuan" class="span2" style="width: 213px;" required="required"');
        $this->load->view('mutasi/pembelian/tambah', $data);
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
            'exp_date' => $_POST['exp_date'],
            'diskon' => $_POST['diskon'],
            'harga_stl_diskon' => $_POST['harga_stl_diskon'],
            'user_id' => $this->user_id
        );
        $ms->form_insert($data);
    }

    function load_data_barang() {
        $ms = new MSementara();
        $data['data_barang'] = $ms->where('user_id', $this->user_id)->get();
        $this->load->view('mutasi/pembelian/ajax/data_barang', $data);
    }

    function simpan() {
        $ms = new MSementara();
        $mb = new MBarang();
        $tgl = strtotime('+' . $_POST['jatuh_tempo'] . ' day', strtotime($_POST['tgl_bukti']));
        $tgl_jt = date('Y-m-d', $tgl);
        
        $data = array(
            'tgl_bukti' => $_POST['tgl_bukti'],
            'no_bukti' => $_POST['no_bukti'],
            'kode_psk' => $_POST['kode_psk'],
            'cara_bayar' => $_POST['cara_bayar'],
            'jatuh_tempo' => $_POST['jatuh_tempo'],
            'tgl_jt' => $tgl_jt,
            'uraian' => $_POST['keterangan'],
            'user_id' => $this->user_id
        );
        //simpan ke table pembelian
        $this->m->simpan_transaksi($data);

        //ambil data barang dari table sementara
        $rs = $ms->where('user_id', $this->user_id)->get();
        foreach ($rs as $row) {
            $this->db->query("INSERT INTO trans_detail_pembelian (no_bukti, kode_brg, jumlah, satuan, harga_beli, diskon, harga_stl_diskon, user_id) 
                VALUES ('" . $_POST['no_bukti'] . "', '$row->kode_brg', '$row->jumlah', '$row->satuan','$row->harga','$row->diskon', '$row->harga_stl_diskon', '$this->user_id')");

            //update stock master barang
            $mb->update_status_stock_barang($row->kode_brg, $row->satuan, $row->jumlah);
        }

        $ms->delete_transaksi($this->user_id);
    }

    function detail_pembelian($no_bukti) {
        $md = new MDetail();
        $data['data_barang'] = $md->where('no_bukti', $no_bukti)->get();
        $this->load->view('mutasi/pembelian/ajax/data_detail_pembelian', $data);
    }

}

?>
