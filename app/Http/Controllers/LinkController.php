<?php

namespace App\Http\Controllers;

use App\Http\Resources\LinkResouce;
use App\Models\Link;
use App\Models\LinkProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LinkController extends Controller
{
    public function index($id){
        $link= Link::with('orders')->where('user_id',$id)->get();
        return LinkResouce::collection($link);
    }
    public  function store(Request $request){
        $link=Link::create([
            'user_id'=>$request->user()->id,
            'code'=>Str::random(6)
        ]);

        foreach ($request->input('products')as $product_id){
            LinkProduct::create([
                'link_id'=>$link->id,
                'product_id'=>$product_id
            ]);
        }
        return $link;
    }
    public function show($code){
        return Link::with('user','products')->where('code',$code)->first();
    }
}
