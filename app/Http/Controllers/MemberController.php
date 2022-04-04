<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

//--yang perlu ditambahkan
use Illuminate\Support\Facades\Validator;
use App\Models\Member;
//--

class MemberController extends Controller
{
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
			'nama' => 'required|string',
			'alamat' => 'required|string',
			'jenis_kelamin' => 'required|string',
			'tlp' => 'required|numeric'
		]);

		if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

		$member = new Member();
		$member->nama = $request->nama;
		$member->alamat = $request->alamat;
		$member->jenis_kelamin = $request->jenis_kelamin;
		$member->tlp = $request->tlp;
		$member->save();

        $data = Member::where('id_member','=', $member->id_member)->first();
        return response()->json([
            'success' => true,
            'message' => 'Data member berhasil ditambahkan!.',
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
			'nama' => 'required|string',
			'alamat' => 'required|string',
			'jenis_kelamin' => 'required|string',
			'tlp' => 'required|numeric'
		]);

		if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ]);
		}

		$member = Member::where('id_member', $id)->first();
		$member->nama = $request->nama;
		$member->alamat = $request->alamat;
		$member->jenis_kelamin = $request->jenis_kelamin;
		$member->tlp = $request->tlp;
		$member->save();

        return response()->json([
            'success' => true,
            'message' => 'Data member berhasil diubah!.'
        ]);
    }

    public function delete($id)
    {
        $delete = Member::where('id_member', $id)->delete();

        if($delete){
            return response()->json([
                'success' => true,
                'message' => 'Data member berhasil dihapus!.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Data member gagal dihapus!.'
            ]);
        }
    }

    public function getAll()
    {
        $data["count"] = Member::count();
        $data["member"] = Member::get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getById($id)
    {   
        $data["member"] = Member::where('id_member', $id)->get();

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
