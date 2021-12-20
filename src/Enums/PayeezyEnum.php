<?php

namespace Smbear\Payeezy\Enums;

class PayeezyEnum
{
    const BANK_SUCCESS_STATUS = [
        100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 164
    ];

    const ERROR = [
        'gateway_08'          => 'Security code is incorrect. Please enter the correct code and try again.',
        'gateway_22'          => 'Invalid account number/incorrect format. Please check the number and try again.',
        'gateway_25'          => 'Invalid Expiry Date. Please check the expiry date or try another payment method.',
        'gateway_26'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_27'          => 'Invalid Cardholder. Please check the name and try again.',
        'gateway_28'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_31'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_32'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_44'          => 'Billing address on the order must match the billing address associated with the card being used.',
        'gateway_57'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_58'          => 'Sorry, you billing address exceeds the limit40 characters). Please try another card or payment method.',
        'gateway_60'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_63'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_64'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_68'          => 'Your card has been restricted. Please try another card or payment method.',
        'gateway_69'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_72'          => 'System error. Please try another payment method or contact your account manager.',
        'gateway_93'          => 'System error. Please try another payment method or contact your account manager.',
        'bank_260'            => 'Service is temporarily unavailable due to network error. Please try later or contact your account manager.',
        'bank_301'            => 'Service is temporarily unavailable due to network error. Please try later or contact your account manager.',
        'bank_302'            => 'Your credit card balance is insufficient. Please try another card.',
        'bank_303'            => 'Generic decline – No other information is being provided by the Issuer.You can contact your card issuing bank or PP customer service to get the specific reason for the transaction failure.',
        'bank_304'            => 'Account is not found. Please check the information or contact the issuing bank.',
        'bank_401'            => 'Issuer wants voice contact with cardholder. Please call your issuing bank.',
        'bank_502'            => 'Card is reported as lost/stolen. Please contact your issuing bank. Note: Does not apply to American Express.',
        'bank_505'            => 'Your account is on negative file. Please try another card or payment method.',
        'bank_509'            => 'Exceeds withdrawal or activity amount limit. Please try another card or payment method.',
        'bank_510'            => 'Exceeds withdrawal or activity count limit. Please try another card or payment method.',
        'bank_519'            => 'Your account is on negative file. Please try another card or payment method.',
        'bank_521'            => 'Total amount exceeds credit limit. Please try another card or payment method.',
        'bank_522'            => 'Your card has expired. Please check the expiry date or try another payment method.',
        'bank_530'            => 'Lack of information provided by issuing bank. Please contact the bank or try another payment method.',
        'bank_531'            => 'Issuer has declined auth request. Please contact your issuing bank or try another payment method.',
        'bank_591'            => 'Issuer error. Please contact the issuing bank or try another card.',
        'bank_592'            => 'Issuer error. Please contact the issuing bank or try another card.',
        'bank_594'            => 'Issuer error. Please contact the issuing bank or try another card.',
        'bank_596'            => 'The transaction was rejected due to risk control by the credit card company.It is recommended that you temporarily stop payment to avoid violating the new risk control regulations and try another card or payment method.',
        'bank_776'            => 'Duplicate Transaction. Please contact your account manager to confirm the transaction status.',
        'bank_787'            => 'Transaction is declined due to high risk. Please try another payment method.',
        'bank_806'            => 'Your card has been restricted. Please try another card or payment method.',
        'bank_825'            => 'Account is not found. Please check the information and try again.',
        'bank_902'            => 'Service is temporarily unavailable due to network error. Please try later or contact your account manager.',
        'bank_904'            => 'Your card is not active. Please contact your issuer bank.',
        'bank_201'            => 'Invalid account number/incorrect format. Please check the number and try again.',
        'bank_204'            => 'Unidentifiable error. please try later or change to another payment method.',
        'bank_233'            => 'Credit card number does not match method of payment type or invalid BIN. Please try another card or payment method.',
        'bank_239'            => 'Card is not supported. Please try another card or choose another payment method.',
        'bank_261'            => 'Invalid account number/incorrect format. Please check the number and try again.',
        'bank_351'            => 'Service is temporarily unavailable due to network error. Please try later or contact your account manager.',
        'bank_755'            => 'Account is not found. Please check the information or contact the issuing bank.',
        'bank_758'            => 'Account is frozen. Please contact your issuing bank or try another payment method.',
        'bank_834'            => 'Card is not supported. Please try another card or payment method.',
        'bank_100'            => 'Billing address does not match the one reserved in your account. Please make sure that these two addresses are exactly the same, or choose another payment method.',
        'bank_504'            => 'Timeout. It is an internal error. Please check your internal server.',
        'bank_614'            => 'The transaction was rejected due to risk control by the credit card company.It is recommended that you temporarily stop payment to avoid violating the new risk control regulations and try another card or payment method.'
    ];
}