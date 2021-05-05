<?php

namespace App\Http\Controllers;

use App\Events\ProductUpdatedEvent;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Ramsey\Collection\Collection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{

    public function index()
    {
        return Product::all();
    }


    public function store(Request $request)
    {
        $product=Product::create($request->only('title','description','image','price'));
        event(new ProductUpdatedEvent);
        return response($product,Response::HTTP_CREATED);
    }


    public function show(Product $product)
    {
        return $product;
    }


    public function update(Request $request, Product $product)
    {
        $product->update($request->only('title','description','image','price'));

        event(new ProductUpdatedEvent);

        return response($product,Response::HTTP_ACCEPTED);
    }


    public function destroy(Product $product)
    {
        $product->delete();

        event(new ProductUpdatedEvent);
        return response(null,Response::HTTP_NO_CONTENT);
    }
    public function frontend(){
        if($product =\Cache::get('product_frontend')){
            return $product;
        }
        $product =Product::all();
        \Cache::set('products_frontend',$product,30*60);
        return $product;
    }
    public  function  backend(Request $request){
        $page=$request->input('page',1);
        /** @var \Illuminate\Support\Collection $product */
        $product=\Cache::remember('products_backend',30*60,fn ()=> Product::all());


        if($s=$request->input('s')){
            $product=$product->filter(fn(Product $product)=>Str::contains($product->title,$s)||Str::contains($product->description,$s));
        }
        $total=$product->count();

        if($sort=$request->input('sort')){
            if($sort==='asc'){
                $product=$product->sortBy([
                    fn($a,$b)=>$a['price']<=>$b['price']
                ]);
            }
            else if($sort==='desc'){
                $product=$product->sortBy([
                   fn($a,$b)=>$b['price']<=>$a['price']
                ]);
            }
        }

        return [
            'data'=>$product->forPage($page,9)->values(),
            'meta'=>[
                'total'=>$total,
                'page'=>$page,
                'last_page'=>ceil($total/9),

            ]
        ];
    }
}
