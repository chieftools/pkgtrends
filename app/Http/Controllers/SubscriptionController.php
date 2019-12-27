<?php

namespace IronGate\Pkgtrends\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use IronGate\Pkgtrends\Http\Requests\SubscribeToReport;
use IronGate\Pkgtrends\Models\Report;
use IronGate\Pkgtrends\Models\Subscription;
use IronGate\Pkgtrends\TrendsProvider;

class SubscriptionController extends Controller
{
    public function postSubscribe(SubscribeToReport $request, $packages): RedirectResponse
    {
        $trends = new TrendsProvider($packages);

        if (!$trends->hasData()) {
            return redirect()->action('TrendsController@showTrends', [$packages]);
        }

        $report = Report::findOrCreate($trends->getHash(), $packages);

        $subscription = Subscription::findOrCreate($request->input('email'), $report);

        if ($subscription->wasRecentlyCreated) {
            $text = "A validation e-mail was send to {$request->input('email')}, check your inbox (and maybe spam) in a few minutes.";
            $type = 'success';
        } else {
            $text = "You're already subscribed to this report, it is send out every week!";
            $type = 'warning';
        }

        return redirect()->action('TrendsController@showTrends', [$packages])->with('message', compact('text', 'type'));
    }

    public function getConfirm(string $id)
    {
        $subscription = Subscription::findByUuid($id);

        if ($subscription === null) {
            return redirect()->action('TrendsController@showTrends')->with('message', [
                'text' => 'That subscription does not exist anymore!',
                'type' => 'danger',
            ]);
        }

        if ($subscription->is_confirmed) {
            return redirect()->action('TrendsController@showTrends', [$subscription->report->packages])->with('message', [
                'text' => 'You already confirmed this subscription!',
                'type' => 'success',
            ]);
        }

        return view('subscription.confirm', compact('subscription'));
    }

    public function postConfirm(string $id): RedirectResponse
    {
        $subscription = Subscription::findOrFailByUuid($id);

        $subscription->confirm();

        return redirect()->action('TrendsController@showTrends', [$subscription->report->packages])->with('message', [
            'text' => 'You successfully confirmed your subscription. Look forward to an e-mail every week!',
            'type' => 'success',
        ]);
    }

    public function getUnsubscribe(string $id)
    {
        $subscription = Subscription::findByUuid($id);

        if ($subscription === null) {
            return redirect()->action('TrendsController@showTrends')->with('message', [
                'text' => 'No subscription found, maybe you\'re already unsubscribed?',
                'type' => 'warning',
            ]);
        }

        return view('subscription.unsubscribe');
    }

    public function postUnsubscribe(string $id): RedirectResponse
    {
        Subscription::findOrFailByUuid($id)->delete();

        return redirect()->action('TrendsController@showTrends')->with('message', [
            'text' => 'Successfully unsubscribed. So long, and thanks for all the fish!',
            'type' => 'success',
        ]);
    }

    public function getUnsubscribeAll(string $email)
    {
        $subscriptions = Subscription::findByEmail($email);

        if ($subscriptions->isEmpty()) {
            return redirect()->action('TrendsController@showTrends')->with('message', [
                'text' => 'No subscriptions found, maybe you\'re already unsubscribed?',
                'type' => 'warning',
            ]);
        }

        return view('subscription.unsubscribe'.($subscriptions->count() > 1 ? '_all' : ''), compact('subscriptions'));
    }

    public function postUnsubscribeAll(string $email): RedirectResponse
    {
        Subscription::findByEmail($email)->each(function (Subscription $subscription) {
            $subscription->delete();
        });

        return redirect()->action('TrendsController@showTrends')->with('message', [
            'text' => 'Successfully unsubscribed for all subscriptions. So long, and thanks for all the fish!',
            'type' => 'success',
        ]);
    }
}
