<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

//--yang perlu ditambahkan
use Illuminate\Support\Facades\Validator;
use App\Models\Paket;
//--

class PaketController extends Controller
{
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'jenis' => 'required|string',
			'harga' => 'required|numeric'
		]);

		if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

		$paket = new Paket();
		$paket->jenis = $request->jenis;
		$paket->harga = $request->harga;
		$paket->save();

        $data = Paket::where('id_paket','=', $paket->id_paket)->first();
        return response()->json([
            'success' => true,
            'message' => 'Data paket berhasil ditambahkan!.',
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
			'jenis' => 'required|string',
			'harga' => 'required|numeric'
		]);

		if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

		$user = Paket::where('id_paket', $id)->first();
		$user->jenis = $request->jenis;
		$user->harga = $request->harga;
		$user->save();

        return response()->json([
            'success' => true,
            'message' => 'Data paket berhasil diubah!.'
        ]);
    }

    public function delete($id)
    {
        $delete = Paket::where('id_paket', $id)->delete();

        if($delete){
            return response()->json([
                'success' => true,
                'message' => 'Data paket berhasil dihapus!.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data paket gagal dihapus!.'
            ]);
        }
    }

    public function getAll()
    {
        $data["count"] = Paket::count();
        $data["paket"] = Paket::get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getById($id)
    {   
        $data["paket"] = Paket::where('id_paket', $id)->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
