<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Emails Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for various emails that
    | we need to display to the user. You are free to modify these
    | language lines according to your application's requirements.
    |
	
	/*
     * admin System error Messages.
     *
     */
	 
	'InterestRecalculationSubject' => 'New Interest Evaluation On Your Margin',
	'InterestRecalculationGreeting' => 'Hello',
	'InterestRecalculationMessage' => 'Due to expiry of your Funding sources, New sources of funding have been automatically assigned to your Margin. A new interesr Rate Has been Applied to satify the funding requirements.',
	'InterestRecalculationButton' => 'Go To @ :site',
    'InterestRecalculationThanks' => 'Regards, :site Team',
    'adminErrorSubject'  => 'System Error @ :site',
    'adminErrorFaGreeting' => 'A Critical Exception was Encountered',
    'adminErrorMessage'  => 'This error requiring your Urgent Attention was encountered at :site. system functionality was disrupted. :message',
   
    'adminErrorThanks'   => 'Thank you!',
	
	
	
	/*
     * service tx Emails
     *
     */

    'newServicetxSubject'  => 'Activity on your account',
    'newServicetxGreeting' => 'Hello! :user',
    'newServicetxMessage'  => 'Please Note, A new :type Transaction of :amount has been executed on your account :number.',
    'newServicetxThanks'   => 'Thank you for your patronage!',
	
	
	
 
	/*
     * two factor auth messages.
     *
     */

    'twoFaSubject'  => 'Authentication required',
    'twoFaGreeting' => 'Welcome!',
    'twoFaMessage'  => 'You need to Authenticate your Login before you can get access to our services.   Your authentication code is  :code  You can also click the link below to Apply the code',
    'twoFaButton'   => 'Authenticate',
    'twoFaThanks'   => 'Thank you for your patronage!',
	
	
	/*
     * trade
     *
     */

    'newTradeSubject'  => 'New Trade Request on Your Ad',
    'newTradeGreeting' => 'Hello!',
    'newTradeMessage'  => 'A new Trade of :qty has been filled on your Ad.  Ref :ref You can also click the link below Take Respective Action',
	'updateTradeMessage'  => 'Your Trade# :ref , Status has Been Updated to :status. Use the link below to Monitor Your Trade Progress',
    'newTradeButton'   => 'Take Action',
    'newTradeThanks'   => 'Thank you for your patronage!',
	
	
		
	/*
     * tradedisputes
     *
     */

    'tradeDisputeSubject'  => 'New Dispute on Your Recent Trade',
    'tradeDisputeGreeting' => 'Hello!',
    'tradeDisputeMessage'  => 'A new Dispute has been filled on your Trade.  Ref :ref. Please Respond to the dispute ASAP',
    'tradeDisputeButton'   => 'View Dispute',
    'tradeDisputeThanks'   => 'Thank you for your patronage!',
	
	/*
     * disputeWon
     *
     */

    'disputeWonSubject'  => 'Your Dispute is Resolved',
    'disputeWonGreeting' => 'Hello!',
    'disputeWonMessage'  => 'A Recent Dispute was resolved in your Favour. Trade Ref :ref. Our sincere Apologies for any inconviniences Caused.',
    'disputeWonButton'   => 'View Resolution',
    'disputeWonThanks'   => 'Thank you for your patronage!',
	
	/*
     * disputeWon
     *
     */

    'disputeLostSubject'  => 'Your Dispute is Resolved',
    'disputeLostGreeting' => 'Hello!',
    'disputeLostMessage'  => 'Thank Your For working to resolve this dispute. We appreciate your  Decision . Dispute on Trade Ref :ref Is Now Resolved. Our sincere Apologies for any inconviniences Caused.',
    'disputeLostButton'   => 'View Resolution',
    'disputeLostThanks'   => 'Thank you for your patronage!',

    /*
     * Activate new user account email.
     *
     */

    'activationSubject'  => 'Activation required',
    'activationGreeting' => 'Welcome!',
    'activationMessage'  => 'You need to activate your email before you can start using all of our services.',
    'activationButton'   => 'Activate',
    'activationThanks'   => 'Thank you for using our application!',

    /*
     * Goobye email.
     *
     */
    'goodbyeSubject'  => 'Sorry to see you go...',
    'goodbyeGreeting' => 'Hello :username,',
    'goodbyeMessage'  => 'We are very sorry to see you go. We wanted to let you know that your account has been deleted. Thank for the time we shared. You have '.config('settings.restoreUserCutoff').' days to restore your account.',
    'goodbyeButton'   => 'Restore Account',
    'goodbyeThanks'   => 'We hope to see you again!',

];
