# Conditional Flat Rate for WooCommerce
#### _Adds conditional Flat Rate shipping method for WooCommerce_

This plugin adds another, slightly altered, instance of the Flat Rate Shipping Method to WooCommerce. This one aims to only show the Flat Rate shipping when certain conditional criteria is met.

### Note ###

The current version focuses on the minimum/maximum criteria and the coupon functionality has not been tested in full. This is a work in progress. Please report any issues.

The current version (now) also changes the **Cost** field to accept a Math expression, rather than just a static cost price. Also a `{subtotal}` tag is available within this field. To add $50 + 20% of subtotal, you could insert:

`150 + ( {subtotal} * 0.2 )`