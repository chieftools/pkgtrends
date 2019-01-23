<?php

namespace IronGate\Pkgtrends\Http\Controllers;

use IronGate\Pkgtrends\Models\Report;
use IronGate\Pkgtrends\TrendsProvider;
use IronGate\Pkgtrends\Models\Subscriber;
use IronGate\Pkgtrends\Http\Requests\SubscribeToReport;

class SubscriptionController extends Controller
{
    public function postSubscribe(SubscribeToReport $request, $packages)
    {
        $trend = new TrendsProvider($packages);

        if (!$trend->hasData()) {
            return redirect()->action('TrendsController@showTrends', [$packages]);
        }

        $report = Report::findOrCreate($trend->getHash(), $packages);

        $subscriber = Subscriber::findOrCreate($request->email, $report);

        if ($subscriber->wasRecentlyCreated) {
            $text = "A validation e-mail was send to {$request->email}, check your inbox (and maybe spam) in a few minutes.";
            $type = 'success';
        } else {
            $text = "You're already subscribed to this report, it is send out every week!";
            $type = 'warning';
        }

        return redirect()->action('TrendsController@showTrends', [$packages])->with('message', compact('text', 'type'));
    }

    public function getConfirm(string $id)
    {
        $subscription = Subscriber::findOrFailByUuid($id);

        if ($subscription->is_confirmed) {
            return redirect()->action('TrendsController@showTrends', [$subscription->report->packages])->with('message', [
                'text' => 'You already confirmed this subscription!',
                'type' => 'success',
            ]);
        }

        return view('subscription.confirm', compact('subscription'));
    }

    public function postConfirm(string $id)
    {
        $subscription = Subscriber::findOrFailByUuid($id);

        $subscription->confirm();

        return redirect()->action('TrendsController@showTrends', [$subscription->report->packages])->with('message', [
            'text' => 'You successfully confirmed your subscription. Look forward to an e-mail every week!',
            'type' => 'success',
        ]);
    }

    public function getUnsubscribe(string $id)
    {
        $subscription = Subscriber::findByUuid($id);

        if ($subscription === null) {
            return redirect()->action('TrendsController@showTrends')->with('message', [
                'text' => 'No subscription found, maybe you\'re already unsubscribed?',
                'type' => 'warning',
            ]);
        }

        return view('subscription.unsubscribe');
    }

    public function postUnsubscribe(string $id)
    {
        $subscription = Subscriber::findOrFailByUuid($id);

        $subscription->delete();

        return redirect()->action('TrendsController@showTrends')->with('message', [
            'text' => 'Successfully unsubscribed. So long, and thanks for all the fish!',
            'type' => 'success',
        ]);
    }

    public function getUnsubscribeAll(string $email)
    {
        $subscriptions = Subscriber::findByEmail($email);

        if ($subscriptions->isEmpty()) {
            return redirect()->action('TrendsController@showTrends')->with('message', [
                'text' => 'No subscriptions found, maybe you\'re already unsubscribed?',
                'type' => 'warning',
            ]);
        }

        return view('subscription.unsubscribe' . $subscriptions->count() > 1 ? '_all' : '', compact('subscriptions'));
    }

    public function postUnsubscribeAll(string $email)
    {
        $subscriptions = Subscriber::findByEmail($email);

        $subscriptions->each->delete();

        return redirect()->action('TrendsController@showTrends')->with('message', [
            'text' => 'Successfully unsubscribed for all subscriptions. So long, and thanks for all the fish!',
            'type' => 'success',
        ]);
    }
}
