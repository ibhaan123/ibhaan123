<?php

return [
    'user_id' => 'User Id',
	'new_dispute'=>'New Dispute on Your Recent Trade # :ref',	
	'won_dispute'=>'Dispute on Trade # :ref is Resolved in Your Favour', 
	'lost_dispute'=>'Dispute on Your Recent Trade # :ref is Now Resolved',
	'country_id' => 'Country Id',
	'from_symbol' => 'From Symbol',
	'to_symbol' => 'To Symbol',
	'type' => 'Type',
	'city' => 'City/Town',
	'feedback'=>'Leave Feed Back',
	'feedback_rating'=>'Rating Feed Back',
	'messages'=>'Messages',
	'custom_required'=>'Please State your Custom Payment Method',
	'rate' => 'Rate',
	'token' => 'Coin',
	'payment'=>"PAYMENT",
	'with' => 'With',
	'location' => 'Location',
	'uselocation' => 'Find Me',
	'Instructions' => 'Buyer Instructions',
	'minutes' => 'Mins',
	'window' => 'Payment Window: (Time (minutes) to Complete payments )',
	'windowp' => 'Time (minutes) required to make payments',
	'windowhelpu' => 'After the Trader accepts to trade, You must send and Confirm Payment before this window elapses. During this time the trade is locked, After this time elapses the trade is open and You or the Trader can Cancel the trade. Unless you confirm the Payment. Donot Confirm Payments YOU Have not sent. You could be be Penalised if a dispute arises',
	'windowhelpt' => 'After the Trader accepts to trade, He should be able to deliver the payments Before this window Elapses, After this time elapses the trade is open and You oCancel the trade. Unless the trader confirm the Payment. Report Traders Who confirm Payments before they sent it.',
	'windowhelp' => 'After You accept a trade, You or the User must Send and Confirm Payment before this window elapses. During this time the trade is locked, After this time, the trade is open and You or the User can Cancel the trade, Unless you confirm the Payment. Donot Confirm Payments YOU Have not sent. You could be Penalised if a dispute arises',
	'complete' =>'I Confirm. I have Sent :coin',
	'escrownotice'=>'Please Read These Instructions to Complete Payment',
	'releasecoin' =>'Release the :coin Escrow',
	'releasenotice'=>"Donot Release :coin From the Escrow Until You have Confirmed Receipt of Payment of :amount This Action is IRREVERSIBLE !!",
	'uselocation_loading'=>'Loading...',
	'more' => 'Learn More',
	'ad_status' => 'Order Status',
	'area' => 'Area',
	'overhead' => 'Profit Overhead. % Margin Over Market Price',
	'overheadt'=>'Overhead',
	'pending'=>'Ad Rate is Empty. This Will Updated Automatically in 3-5 minutes <br> And Your Advert Activated ',
	'overheadp' => 'Profit Overhead in % eg 4',
	'min' => 'Min Coins',
	'max' => 'Max Coins',
	'custom_method'=>'Or Custom Payment Method',
	'custome_type' => 'Custom Type',
	'online' => 'Online',
	'offline' => 'Offline',
	'ad' => 'Ad',
	'buy' => 'Buy',
	'buy_now'=>'Buy Now',
	'sell' => 'Sell',
	'sell_now'=>'Sell Now',
	'from' => 'From',
	'to' => 'To',
	'cancel_trade'=>'Cancel This Trade',
	'dispute_trade'=>'Dispute This Trade',
	'accept_trade'=>'Accept and Lock',
	'reject_trade'=>'Reject This Trade',
	'no_offers'=>'No Offers Available',
	'extrainformation'=>'Message to Trader',
	'extrainformationp'=>'Payment Detail , Extra Information etc',
	'pricehelp' => 'Margin calculated want over the market price. Use a a negative value to buy or sell under the market price to attract more contacts. Price is Updated Every Hour ',
	'accounthelp'=>'This is the account of Your Payment method. You will send the users funds to this address or account. If you are selling Cryptos, you can use this to quickly indentify the payer when you recieve funds',
	'preview'=>'Preview',
	'user_validation' =>'User Min Validation',
	'verified_phone' =>'Verified Phone',
	'verified_id' =>'Verified ID/Passport',
	'min_vol' =>'Users Min Total Volume (USD)',
	'min_count' =>'Users Min Number of Trades',
	'both' => 'Both',
	'Ad' => 'Offer',
	'ads' => 'Offers',
	'method' => 'Method',
	'payment_methods'=>'Available Payment methods',
	'instructions' => 'Terms and conditions To User',
	'no_account'=>"Account Info",
	'not_relevant'=>'Not Relevant',
	'account' => 'Account to Pay To Or  Account Sending Payments. Eg Paypal email , Bank Account etc',
	'accountp' => 'User will Be Required To Provide this Account If Set.',
	'status' => 'Status',
	'active' => 'Active',
	'payment_method' => 'Payment method',
	'seller' => 'Seller',
	'buyer' => 'Buyer',
	'price_per' => 'Price / Coin (:showing)',
	'limits' => 'Limits',
	'unverified' => 'Unverified',
	'verified'=>'Verified',
	'email' => 'Email',
	'phone' => 'Phone',
	'account_age' => 'Account Created',
	'has_trades' => 'Trade Count',
	'has_trades_with_me' => 'Trades With Me',
	'otherads'=>"Other Ads",
	'tos'=>'Terms of trade with :name',
	'how-to-trade'=>'Trading Tips',
	'cancelling-trade'=>'Learn How You can cancel',
	'how-to-list'=>'How to Create A new Offer',
	'creating-a-dispute'=>'Creating A trade Dispute',
	// bu_tip = user is buyer
	// su_tip = user is seller
	// bt_tip = trader is buyer
	// st_tip = trader is seller
	'open'=>[
		'bu_tip'=>'Trade Window has expired. You or the Trader can Cancel this Trade, if You so Wish!, Before Payment Confirmation is provided',
		'bt_tip'=>'Trade Window has expired. You or the User Cancel this Trade, if You so Wish! Before Payment Confirmation is provided',
		'su_tip'=>'Trade Window has expired. You or the Trader can Cancel this Trade, if You so Wish!, Before Payment Confirmation is provided',
		'st_tip'=>'Trade Window has expired. You or the User Cancel this Trade, if You so Wish! Before Payment Confirmation is provided',
		'badge'=>'warning',
		], 
	'closed'=> [
		'bu_tip'=>'Dispute has been settled. Thanks to You.',
		'bt_tip'=>'Dispute has been settled. Thanks to You.',
		'su_tip'=>'Dispute has been settled. Thanks to You.',
		'st_tip'=>'Dispute has been settled. Thanks to You.',
		'badge'=>'success',
		],
	'locked'=> [
		'bu_tip'=>'Trader has accepted this Transaction. You must complete  within The Requested Time Window',
		'bt_tip'=>'You are Processing this Transaction. Please Complete Payments Within Your Window',	
		'su_tip'=>'Trader has accepted this Transaction. You must complete  within The Requested Time Window',
		'st_tip'=>'You are Processing this Transaction. Please Complete Payments Within Your Window',	
		'badge'=>'primary',
		], 
	'locked_seller'=> [
		'bu_tip'=>'Trader has accepted this Transaction. Rememebr Once you release The Escrow, The Transaction CANNOT be reversed',
		'bt_tip'=>'You are Processing this Transaction. Rememebr Once you release The Escrow, The Transaction CANNOT be reversed',
		'su_tip'=>'Trader has accepted this Transaction. Rememebr Once you release The Escrow, The Transaction CANNOT be reversed',
		'st_tip'=>'You are Processing this Transaction. Rememebr Once you release The Escrow, The Transaction CANNOT be reversed',		
		'badge'=>'primary',
		], 
	'pending'=> [
		'bu_tip'=>'Trader is yet to Respond to this Request. DONOT SEND PAYMENT UNTILL THE TRADER ACCEPTS YOUR TRADE. Escrow is NOT active. You Send Payment at Your Risk <br> Allow the Trader his Reponse Window First',
		'bt_tip'=>'Please Accept this request to seal the Deal',
		'su_tip'=>'Trader is yet to Respond to this Request. DONOT SEND PAYMENT UNTILL THE TRADER ACCEPTS YOUR TRADE. Escrow is NOT active. You Send Payment at Your Risk <br> Allow the Trader his Reponse Window First',
		'st_tip'=>'Please Accept this request to seal the Deal',
		'badge'=>'secondary',
		],  // 
	'rejected'=> [
		'bu_tip'=>'Trader has rejected this Request. Please Look throu',
		'bt_tip'=>'You rejected this Request. You Dont have to take any Other actions',
		'su_tip'=>'Trader has rejected this Request. Please Look throu',
		'st_tip'=>'You rejected this Request. You Dont have to take any Other actions',
		'badge'=>'danger',
		],  
	'paid'=> [
		'bu_tip'=>'You have Sent Payment! The Trader will Verify This Payment and Release The Escrow soon. Please Allow them the transaction window',
		'bt_tip'=>'You have Sent Payment!! Allow The User Time to Verify This Payment , and Release The Escrow',
		'su_tip'=>'Trader has Made Payment! Please Verify This Payment and Release The Escrow',
		'st_tip'=>'You Have Been Paid!! Verify This Payment , and Release The Escrow',
		'badge'=>'primary',
		],    
	'success'=> [
		'bu_tip'=>'You Made A Purchase!!. Happy Crypto Spending!!. Trade has been Completed Successfully. Trade has been Completed Successfully, Thanks to You',
		'bt_tip'=>'You Made A Purchase!! Trade has been Completed Successfully. Thanks To you',
		'su_tip'=>'You Made a Sale!. Pheeww !! .Trade has been Completed Successfully, Thanks to You. Happy Shopping',
		'st_tip'=>'You Made a Sale!. Pheeww !! . Trade has been Completed Successfully. Thanks To you',
		'badge'=>'success',
		],
	'disputed'=>[
		'bu_tip'=>'Your Payment is being disputed. Please Use the Chat System system to resolve this. our Team will closely monitor this conversation and Step in.',
		'bt_tip'=>'Your Payment is being disputed. Please Use the Chat System system to resolve this. our Team will closely monitor this conversation and Step in.',
		'su_tip'=>'This Trade is being disputed. Releasing The ESCROW will settle this dispute. Please Respond Using the Chat System',
		'st_tip'=>'This Trade is being disputed. Releasing The ESCROW will settle this dispute. Please Respond Using the Chat System',
		'badge'=>'warning',
		],// issue has been raised.
	'ignored'=>[
		'bu_tip'=>'Trader did not respond within the requested Window',
		'bt_tip'=>'You did not respond within Your requested Time Window',
		'su_tip'=>'Trader did not respond within the requested Window',
		'st_tip'=>'You did not respond within Your requested Time Window',
		'badge'=>'warning',
	],
	'cancelled'=>[
		'su_tip'=>'This Trade was Cancelled. You Look through Our Ads and initiate another Trade',
		'st_tip'=>'This Trade is Cancelled. You cannot Proceed with the Order. Please Contact the client to initiate a new Trade',
		'bu_tip'=>'This Trade was Cancelled. You Look through Our Ads and initiate another Trade',
		'bt_tip'=>'This Trade is Cancelled. You cannot Proceed with the Order. Please Contact the client to initiate a new Trade',
		'badge'=>'danger',
	],
	'cant_open'=>'You Cannot Mark Trades as Open',
	'isclosed'=>'You Have successfully Resolved this dispute',
	'cant_lock'=>'You Can only Lock Pending trades',
	'islocked'=>'You Have successfully Locked this Trade. Remember to Deliver Payments Before Window Elapses',
	'cant_lock_forbidden'=>'You Cannot Lock this Trade',
	'cant_open'=>'You Cannot Mark Trades as Open',
	'cant_pending' =>'You Cannot Mark Trades as Pending',
	
	'cant_reject'=>'You Can only Reject Pending trades',
	'isrejected'=>'You Have successfully Rejected this Trade.',
	'cant_reject_forbidden'=>'You Cannot Reject this Trade',
	'cant_dispute'=>'You Can only Dispute Paid Trades Trades, Before the Escrow Is Released',
	'isrejected'=>'You Have successfully Rejected this Trade.',
	
	'cant_success'=>'You Dont Have the Ability to Release This Escrow',
	'issuccess'=>'You Have successfully Released The Escow. Transaction Complete.',
	'cant_paid'=>'You Dont Have the Ability to Mark This as Paid',
	'ispaid'=>'You Have successfully Marked Payment as Complete.',
	'cant_ignored'=>'This Action is Not allowed',
	'cant_cancel'=>'You Dont Have the Permissions to Cancel this Order',
	'iscancelled'=>'You Have successfully Cancelled This Order.',
	'unKnownUpdates'=>'Order Update Failed. Unknown Action Step! Requested',
	'invalid_permission'=>'Forbidden. You cannot Perform This action'
];