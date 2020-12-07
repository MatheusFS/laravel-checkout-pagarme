<?php

namespace MatheusFS\Laravel\Checkout\Facades;

use App\Models\Marketplace\Product;
use MatheusFS\Laravel\Checkout\Mail\Postback\Customer as PostbackToCustomer;
use MatheusFS\Laravel\Checkout\Mail\Postback\Development as PostbackToDevelopment;
use MatheusFS\Laravel\Checkout\Mail\Postback\Supplier as PostbackToSupplier;
use MatheusFS\Laravel\Checkout\Payment\Gateways\PagarMe\Status;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class Mailer {

    public static function copies(){
        
        $default_from = env('MAIL_FROM_ADDRESS', 'example@domain.com');
        $default_to = env('MAIL_TO_ADDRESS', $default_from);
        return config('checkout.copies', [ $default_to ]);
    }

    /**
     * Send transaction status update for customer entity
     * 
     * @param string $email
     * @param \MatheusFS\Laravel\Checkout\Mail\Postback\Customer $mailable
     */
    public static function mailCustomer($email, $mailable) {

        $recipients = array_merge([$email], self::copies());

        Mail::to($recipients)->send($mailable);
        Log::info("Sent mail to $email and copies to " . implode(', ', self::copies()) . '.');
    }

    public static function mailSuppliers($normalized){
        
        foreach($normalized['items'] as $item){
    
            $supplier_id = Product::find($item['id'])->supplier->getKey();
            $suppliers[$supplier_id]['items'][] = $item;
        }

        foreach($suppliers as $supplier_id => $items){
    
            $supplier = config('checkout.supplier.model')::find($supplier_id);

            $normalized['supplier'] = [
                'email' => $supplier->{config('checkout.supplier.property_mapping.email')},
                'name' => $supplier->{config('checkout.supplier.property_mapping.name')},
                'logo' => $supplier->{config('checkout.supplier.property_mapping.logo')}
            ];

            $normalized['items'] = $items;

            $supplier_mailable = self::getSupplierMailable($normalized);
            self::mailSupplier($normalized['supplier']['email'], $supplier_mailable);
        }
    }

    /**
     * Send transaction status update for supplier entity
     * 
     * @param string $email
     * @param \MatheusFS\Laravel\Checkout\Mail\Postback\Supplier $mailable
     */
    public static function mailSupplier($email, $mailable){
        
        $recipients = array_merge([$email], self::copies());

        Mail::to($recipients)->send($mailable);
        Log::info("Sent mail to $email");
    }

    public static function getCustomerMailable($normalized){
        
        $status = $normalized['status'];

        $status = [
            'subject' => Status::subject($status),
            'alias' => Status::as($status),
            'instruction' => Status::instruction($status)
        ];

        return new PostbackToCustomer(
            $normalized['customer'],
            $normalized['shipping'],
            $normalized['items'],
            $status,
            $normalized['payment_method'],
            $normalized['boleto']
        );
    }

    public static function getSupplierMailable($normalized){
        
        $status = $normalized['status'];

        $status = [
            'subject' => Status::subject($status),
            'alias' => Status::as($status),
            'instruction' => Status::instruction($status)
        ];

        return new PostbackToSupplier(
            $normalized['supplier']['name'],
            $normalized['supplier']['logo'],
            $normalized['customer'],
            $normalized['shipping'],
            $normalized['items'],
            $status,
            $normalized['payment_method']
        );
    }
}