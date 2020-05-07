# laravel-checkout-pagarme
Laravel checkout facade to pagar.me v4 api

## Simple payment link redirect

```php
public function buy(Request $request, $id) {

    # Request creates objects from inputs name
    $shipping = (object) $request->shipping;
    $customer = (object) $request->customer;

    $item = Product::find($id); # your product model goes here

    # Normalize address w/ Pagar.me v4 API
    $address = new Address(
        $shipping->street, 
        $shipping->street_number, 
        $shipping->zipcode, 
        'br', # Default country, if you operate w/ more than one country you can modify the request to ask user 
        $shipping->state, 
        $shipping->city,
        $shipping->neighborhood,
        $shipping->complementary
    );

    # Normalize shipping w/ Pagar.me v4 API
    $Shipping = new Shipping($shipping->name, $address, (float) $shipping->fee*100, new DateTime());

    # Initiate facade 
    $pagarme = new CheckoutFacade();

    # Normalize customer and billing w/ Pagar.me v4 API
    $pagarme->setCustomer($customer->name, $customer->cpf, $customer->phone_number, $customer->email);
    $pagarme->setBilling('Entrega', $address);
    $pagarme->setShipping($Shipping);

    # Add normalized item to checkout
    $pagarme->addItem($item->id, $item->label, $item->price);

    # Redirect the user to the generated payment link
    return redirect($pagarme->getPaymentLink($item->price*100 + (float)$shipping->fee*100));
    }
```