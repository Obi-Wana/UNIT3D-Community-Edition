<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Controllers;

use App\Http\Requests\StoreDonationTransactionRequest;
use App\Models\DonationItem;
use App\Models\DonationTransaction;
use Illuminate\Support\Facades\Redirect;
use PrevailExcel\Nowpayments\Facades\Nowpayments;
use Exception;

class DonationTransactionController extends Controller
{
    /**
     * Collect Order data and create Payment.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(StoreDonationTransactionRequest $request)
    {
        $user = $request->user();

        // Validate
        $request->validated();

        try {
            $price = DonationItem::query()->select(['price_usd'])->where('id', '=', $request->id)->value('price_usd');

            $data = [
                'price_amount'   => $price ?? 100,
                'price_currency' => $request->fiat ? strtolower($request->fiat) : 'usd',
                'order_id'       => $request->order_id ? strtolower($request->order_id) : uniqid(),
                'pay_currency'   => $request->coin ? strtolower($request->coin) : 'btc',
                'success_url'    => config('app.url').'/pages/donate?crypto=true&success=true',
                'cancel_url'     => config('app.url').'/pages/donate?crypto=true&success=false',
            ];

            $paymentDetails = Nowpayments::createInvoice($data);

            $transaction = DonationTransaction::create([
                'user_id'                => $user->id,
                'donation_item_id'       => $request->id,
                'nowpayments_invoice_id' => $paymentDetails['id'],
                'nowpayments_order_id'   => $paymentDetails['order_id'],
                'currency'               => $paymentDetails['pay_currency'],
                'confirmed'              => 0,
            ]);

            return Redirect::to($paymentDetails['invoice_url']);
        } catch(Exception $e) {
            return to_route('donate')
                ->withError('Theres an error in the data!');
        }
    }
}
