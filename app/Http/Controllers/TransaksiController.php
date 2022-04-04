<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

//--yang perlu ditambahkan
use Illuminate\Support\Facades\Validator;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use DB;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

//--

class TransaksiController extends Controller
{
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'id_member'         => 'required|numeric',
            'tgl'               => 'required|date',
            'lama_pengerjaan'   => 'required|numeric',
            'id_user'           => 'required|numeric',

		]);

		if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

        //menghitung data batas waktu
        $tgl_transaksi = date_create($request->tgl);
        date_add($tgl_transaksi, date_interval_create_from_date_string($request->lama_pengerjaan." days"));
        $batas_waktu = date_format($tgl_transaksi, 'Y-m-d');


		$transaksi = new Transaksi();
		$transaksi->id_member = $request->id_member;
        $transaksi->tgl = $request->tgl;
        $transaksi->batas_waktu = $batas_waktu;
        $transaksi->id_user = $request->id_user;
		$transaksi->save();

        //insert detail transaksi
        for($i = 0; $i < count($request->detail); $i++){
            $detail_transaksi = new DetailTransaksi();
            $detail_transaksi->id_transaksi = $transaksi->id_transaksi;
            $detail_transaksi->id_paket = $request->detail[$i]['id_paket'];
            $detail_transaksi->berat = $request->detail[$i]['berat'];
            $detail_transaksi->save();
        }

        $data = Transaksi::where('id_transaksi','=', $transaksi->id_transaksi)->first();

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil ditambahkan!.',
            'data' => $data
        ]);
    }

    public function update_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'id_transaksi'      => 'required|numeric',
            'status'            => 'required|string',
		]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

        $transaksi = Transaksi::where('id_transaksi', $request->id_transaksi)->first();
		$transaksi->status = $request->status;
		$transaksi->save();

        return response()->json([
            'success' => true,
            'message' => 'Data transaksi berhasil diubah menjadi '.$request->status,
        ]);

    }

    public function update_bayar(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'id_transaksi'      => 'required|numeric',
            'dibayar'           => 'required|string',
		]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

        $transaksi = Transaksi::where('id_transaksi', $request->id_transaksi)->first();
		$transaksi->status= $request->dibayar;

        if($request->dibayar == 'dibayar'){
            $transaksi->tgl_bayar = date('Y-m-d');
        } else {
            $transaksi->tgl_bayar = NULL;
        }
        
		$transaksi->save();

        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil diubah menjadi '.$request->dibayar,
        ]);

    }

    public function report(Request $request)
    {
        //$validator = Validator::make($request->all(), [
			//'tahun' => 'required|numeric',
		//]);

		//if($validator->fails()){
           // return $this->response->errorResponse($validator->errors());
		//}

        $user = JWTAuth::parseToken()->authenticate();

        $query = DB::table('transaksi')
                    ->select('transaksi.id_transaksi', 'transaksi.tgl', 'transaksi.status', 'transaksi.dibayar', 'transaksi.tgl_bayar', 'users.nama as nama_user', 'member.nama as nama_member')
                    ->join('users', 'users.id', '=', 'transaksi.id_user')
                    ->join('outlet', 'outlet.id_outlet', '=', 'users.id_outlet')
                    ->join('member', 'member.id_member', '=', 'transaksi.id_member')
                    ->where('users.id_outlet', '=', $user['id_outlet'])
                    ->whereYear('transaksi.tgl', '=', $request->tahun)
                    ;

        if($request->bulan != NULL){
            $query->WhereMonth('transaksi.tgl', '=', $request->bulan);
        }
        if($request->tgl != NULL){
            $query->WhereDay('transaksi.tgl', '=', $request->tgl);
        }
        
        if(count($query->get()) > 0){
            $data['status'] = true;
            $i = 0;
            foreach($query->get() as $list){
                //get total transaksi
                $get_total_transaksi = DB::table('detail_transaksi')
                                        ->select('detail_transaksi.id_detail_transaksi', 'detail_transaksi.id_paket', 'paket.jenis', 'detail_transaksi.berat' ,DB::raw('paket.harga*detail_transaksi.berat as sub_total'))
                                        ->join('paket', 'paket.id_paket', "=", "detail_transaksi.id_paket")
                                        ->where('detail_transaksi.id_transaksi', '=', $list->id_transaksi)
                                        ->get();
                $total = 0;
                foreach($get_total_transaksi as $sub_total){
                    $total += $sub_total->sub_total;
                }

                $data['data'][$i]['id_transaksi'] = $list->id_transaksi;
                $data['data'][$i]['tgl'] = $list->tgl;
                $data['data'][$i]['status'] = $list->status;
                $data['data'][$i]['dibayar'] = $list->dibayar;
                $data['data'][$i]['tgl_bayar'] = $list->tgl_bayar;
                $data['data'][$i]['kasir'] = $list->nama_user;
                $data['data'][$i]['nama_member'] = $list->nama_member;
                $data['data'][$i]['total'] = $total;
                $data['data'][$i]['detail_transaksi'] = $get_total_transaksi;

                $i++;
            }
        } else {
            $data['status'] = false;
            $data['data'] = NULL;
        }

        return response()->json($data);
    }



}
