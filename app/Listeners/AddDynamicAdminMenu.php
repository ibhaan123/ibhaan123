<?php

namespace App\Listeners;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddDynamicAdminMenu
{
	
	
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
	
    /**
     * Handle the event.
     *
     * @param  BuildingMenu  $event
     * @return void
     */
    public function handle(BuildingMenu $event)
    {
		$event->menu->add(__('admin.main_navigation'));
		$event->menu->add([
			'text'        => __('admin.bashboard'),
			'url'         => 'admin',
			'icon'        => 'home',
			'label'       => 4,
			'label_color' => 'success',
		]);
		$event->menu->add([
			'text'    => __('admin.members'),
			'icon'    => 'users',
			'submenu' => [ 
					[
						'text'        => __('admin.users')	,
						'url'         => 'admin/users',
						'icon'        => 'user'
					],
					[
						'text'        => __('admin.new_user'),
						'url'         => 'admin/users/create',
						'icon'        => 'user'
					],
					
					[
						'text'        => __('admin.roles'),
						'url'         => route('admin.roles.index'),
						'icon'        => 'balance-scale'
					],
					[
						'text'        => __('admin.add_roles'),
						'url'         => route('admin.roles.create'),
						'icon'        => 'edit'
					]
						
					
			]
		]);
	
		$event->menu->add(__('admin.blockchain'));
		$event->menu->add([
			'text'    => __('admin.coins_admin'),
			'icon'    => 'exchange',
			'submenu' => [
					[
						'text' => __('admin.coins_and_ico'),
						'url'  => 'admin/tokens',
						'icon' => 'cog',
					],
					[
						'text' => __('admin.add_coins_or_ico')	,
						'url'  => 'admin/tokens/create',
						'icon' => 'cog',
					],
					
					[
						'text' => __('admin.wallets')	,
						'url' => route('admin.wallets.index'),
						'icon' => 'navicon',
					]
			]
		]);
		
	

		
		$event->menu->add([
			'text'    => __('app.exchange'),
			'icon'    => 'exchange',
			'submenu' => [
				[
					'text' => __('admin.ads'),
					'url'  => route('admin.ads.index'),
					'icon' => 'car',
				],
				[
					'text' => __('admin.orders'),
					'url'  => route('admin.trades.index'),
					'icon' => 'shopping-cart',
				],
				[
					'text' =>  __('admin.accounts'),
					'url' => route('admin.services.index'),
					'icon' => 'credit-card',
				 ],
				 [
					'text' => __('admin.transactions')	,
					'url' => route('admin.service_txs.index'),
					'icon' => 'exchange',
				 ]
				,
				 [
					'text' => __('admin.chats')	,
					'url' => route('admin.chats.index'),
					'icon' => 'comments',
				 ]
				,
				 [
					'text' => __('admin.disputes')	,
					'url' => route('admin.disputes.index'),
					'icon' => 'legal',
				 ]
				,
				 [
					'text' => __('app.feedbacks')	,
					'url' => route('admin.feedbacks.index'),
					'icon' => 'comment',
				 ]
				,
				 [
					'text' => __('admin.deposit_withdraw')	,
					'url' => route('admin.ios.index'),
					'icon' => 'random',
				 ]
			]
		]);
		
	
		 
	

		$event->menu->add(__('admin.my_account'));
		$event->menu->add([
			'text' => __('admin.edit_my_acccount')	,
			'url' => 'admin/users/'.\Auth::user()->id.'/edit',
			'icon' => 'user',
		]);
		$event->menu->add([
			'text' => __('admin.config')	,
			'url' => route('admin.config.show'),
			'icon' => 'cogs',
		]);
		
		$event->menu->add([
			'text' => __('vouchers.vouchers')	,
			'url' => route('admin.vouchers.index'),
			'icon' => 'ticket',
		]);
		$event->menu->add(__('admin.info'));
		$event->menu->add([
			'text'       => date('D d M Y'),
			'icon_color' => 'red',
		]);
		$event->menu->add([
			'text'       => __('admin.app_name'),
			'icon_color' => 'yellow',
		]);
	}
}
