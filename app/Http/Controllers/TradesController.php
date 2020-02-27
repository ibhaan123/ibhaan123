<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Trade;
use App\Traits\TradeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use  Yajra\DataTables\DataTables;

class TradesController extends Controller
{
	use TradeTrait;
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
		return view('_admin.trades.index');
    }

    

  

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $trade = Trade::with(['user','ad.user','token','country'])->where('uuid',$id)->firstOrFail(); 
		$dialogue = \Chat::conversations()->getById($trade->chat_id);
		$messages = $dialogue->messages;
		$messages->load('sender');
        return view('_admin.trades.show', compact('trade','messages'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $trade = Trade::findOrFail($id);

        return view('_admin.trades.edit', compact('trade'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $request->validate( [
			'message' => 'required|string',
			'award' => 'required|string',
		]);
        $trade = Trade::with(['ad','user','dispute'])->where('uuid',$id)->firstOrFail();
		$conversation = \Chat::conversations()->getById( $trade->chat_id);
		$admin = auth()->user();
		$conversation->users()->syncWithoutDetaching([$admin->id]);;
		if ($conversation->private && $conversation->users->count() > 2) {
            $conversation->private = false;
            $conversation->save();
        }
		$message = \Chat::message($request->message)
            ->from($admin)
            ->to($conversation)
            ->send();
		if($request->award !='message'){
			$winner = $request->award =='trader'?$trade->ad->user:$trade->user;
			try{
				$this->settle_dispute($winner->user, $trade);
			}catch(Exception $e){
				return response()->json(['status' => 'ERROR','URL'=>route('admin.trades.show',$trade->uuid), 'message' =>$e->getMessage()]);
			}
			$trade->escrow = NULL;
			$trade->status = 'closed';
			$trade->save();
			return response()->json(['status' => 'SUCCESS', 'URL'=>route('admin.trades.show',$trade->uuid),'message' =>__('ads.isclosed')]);
			
		}else{
			return response()->json(['status' => 'SUCCESS','URL'=>route('admin.trades.show',$trade->uuid),'message' =>__('admin.message_sent')]);
		}
		
		
		
		
     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {
        Trade::destroy($id);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('trades.Trade'),'action'=> __('admin.Deleted')])]);

    }
	
	  /**toggle Item status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_status($id)
    {
        $trade = Trade::findOrFail($id);
		$trade->status = $trade->status  == 0? 1:0;
		$trade->save();
		$action= $trade->status == 1 ?  __('admin.Activated'): __('admin.Deactivated');
		
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('trades.Trade'),'action'=> $action ])]);
       
    }
	
	/**
     * Remove the specified resources from storage.
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Request $request)
    {
		if(!count($request->ids))
		return response()->json(['status' => 'SUCCESS','message' => __('admin.nothing_selected')]);
        Voucher::destroy($request->ids);
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('trades.Trade'),'action'=> __('admin.Deleted')])]);

    }
	
	  /**toggle Item status.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function toggle_statuses(Request $request)
    {
		if(!count($request->ids))

		return response()->json(['status' => 'SUCCESS','message' => __('admin.nothing_selected')]);
        Trade::where('status','!=', 3)->whereIn('id', $request->ids)->update(['status'=>$request->status]);
		$action= $request->status == 1 ?  __('admin.Activated'): __('admin.Deactivated');
		return response()->json(['status' => 'SUCCESS','message' => __('admin.action_ok',['item'=>__('trades.Trade'),'action'=> $action ])]);
    }
	
	/**
     * Get the Table.
     *
     * 
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	 public function table(){
		 $trade = Trade::with(['ad','token','user','trader'])->get();
		return DataTables::of($trade)
		->rawColumns(['ad_id','actions','badge'])
		->editColumn('ad_id',function($item){
			return $item->type;
		})
		
		->editColumn('user',function($item){
			return $item->user->name;
		})
		->editColumn('trader',function($item){
			return $item->trader->name;
		})
		->editColumn('qty',function($item){
			return $item->qty.$item->ad->from_symbol;
		})
		->editColumn('total',function($item){
			return $item->total.$item->ad->to_symbol;
		})
		->addColumn('actions',function($item){
			$links='<a href="'.route('admin.trades.show',$item->uuid).'" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i></a> <a class="btn btn-success btn-sm"  href="'.route('admin.ads.edit',$item->ad->id).'" ><i class="fa fa-reply"></i>Ad</a>';
			return $links;
		})
		->addColumn('badge',function($item){
			return $item->badge;
		})
		->toJson();
	}
}
