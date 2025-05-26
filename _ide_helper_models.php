<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $avatar_url
 * @property string|null $phone_number
 * @property \Illuminate\Support\Carbon|null $last_password_change
 * @property int $failed_login_count
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property bool $is_active
 * @property bool $two_factor_verified
 * @property string $two_factor_method
 * @property array<array-key, mixed>|null $backup_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_enabled_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupportTicket> $assignedSupportTickets
 * @property-read int|null $assigned_support_tickets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdminAuditLog> $auditLogs
 * @property-read int|null $audit_logs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Admin> $createdAdmins
 * @property-read int|null $created_admins_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $createdProducts
 * @property-read int|null $created_products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shipment> $createdShipments
 * @property-read int|null $created_shipments_count
 * @property-read Admin|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $handledOrders
 * @property-read int|null $handled_orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $inventoryUpdates
 * @property-read int|null $inventory_updates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vendor> $managedVendors
 * @property-read int|null $managed_vendors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $moderatedReviews
 * @property-read int|null $moderated_reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceHistory> $priceHistories
 * @property-read int|null $price_histories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $processedPayments
 * @property-read int|null $processed_payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PromoCode> $promoCodes
 * @property-read int|null $promo_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $updatedProducts
 * @property-read int|null $updated_products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shipment> $updatedShipments
 * @property-read int|null $updated_shipments_count
 * @method static \Database\Factories\AdminFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereBackupCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereFailedLoginCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereLastPasswordChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePasswordHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereTwoFactorEnabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereTwoFactorMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereTwoFactorVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin withoutTrashed()
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $admin_id
 * @property string $action
 * @property string $resource
 * @property int $resource_id
 * @property array<array-key, mixed>|null $previous_data
 * @property array<array-key, mixed>|null $new_data
 * @property string $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $admin
 * @method static \Database\Factories\AdminAuditLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereNewData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog wherePreviousData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereResource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereResourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdminAuditLog withoutTrashed()
 */
	class AdminAuditLog extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Article query()
 */
	class Article extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $carts_expiry
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Database\Factories\CartFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCartsExpiry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart withoutTrashed()
 */
	class Cart extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read \App\Models\Cart|null $cart
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem withoutTrashed()
 */
	class CartItem extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_category_id
 * @property string|null $image_url
 * @property string|null $category_description
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property bool $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property-read \App\Models\Article|null $article
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Comment query()
 */
	class Comment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $quantity
 * @property int $reserved_quantity
 * @property string $location
 * @property \Illuminate\Support\Carbon|null $last_restocked
 * @property int $low_stock_threshold
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Admin|null $updatedBy
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Database\Factories\InventoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereLastRestocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereLowStockThreshold($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereReservedQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory withoutTrashed()
 */
	class Inventory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $shipping_street
 * @property string $shipping_city
 * @property string $shipping_state
 * @property string $shipping_postal_code
 * @property string $shipping_country
 * @property string $billing_street
 * @property string $billing_city
 * @property string $billing_state
 * @property string $billing_postal_code
 * @property string $billing_country
 * @property numeric $total_amount
 * @property \Illuminate\Support\Carbon $order_date
 * @property string $status
 * @property numeric $discount_amount
 * @property int|null $promo_code_id
 * @property \Illuminate\Support\Carbon|null $expected_delivery_date
 * @property string|null $admin_notes
 * @property int|null $handled_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $handledBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\PromoCode|null $promoCode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Shipment> $shipments
 * @property-read int|null $shipments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupportTicket> $supportTickets
 * @property-read int|null $support_tickets_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereAdminNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingPostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereBillingStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereExpectedDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereHandledBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order wherePromoCodeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingPostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereShippingStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withoutTrashed()
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property int $quantity
 * @property numeric $price
 * @property numeric $tax_rate
 * @property numeric $tax_amount
 * @property string|null $tax_name
 * @property string|null $tax_region
 * @property numeric $discount_amount
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Database\Factories\OrderItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereDiscountAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTaxAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereTaxRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderItem withoutTrashed()
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $admin_id
 * @property string $token_hash
 * @property string $request_ip
 * @property \Illuminate\Support\Carbon $expires_at
 * @property int $is_used
 * @property \Illuminate\Support\Carbon|null $used_at
 * @property string $reset_type
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Database\Factories\PasswordResetTokenFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereAdminId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereIsUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereRequestIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereResetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereTokenHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordResetToken withoutTrashed()
 */
	class PasswordResetToken extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property numeric $amount
 * @property string $payment_method
 * @property string $payment_processor
 * @property string $transaction_id
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $refund_id
 * @property string|null $refund_reason
 * @property int|null $processed_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Admin|null $processedBy
 * @method static \Database\Factories\PaymentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentProcessor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereProcessedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRefundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereRefundReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment withoutTrashed()
 */
	class Payment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $variant_id
 * @property numeric $previous_price
 * @property numeric $new_price
 * @property string $updated_at
 * @property int $changed_by
 * @property string|null $reason
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin $admin
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\ProductVariant|null $variant
 * @method static \Database\Factories\PriceHistoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereChangedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereNewPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory wherePreviousPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriceHistory withoutTrashed()
 */
	class PriceHistory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property numeric $price
 * @property string $sku
 * @property int|null $vendor_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string $status
 * @property numeric|null $weight
 * @property string|null $dimensions
 * @property string|null $warranty_info
 * @property string|null $return_policy
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $inventory
 * @property-read int|null $inventory_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceHistory> $priceHistory
 * @property-read int|null $price_history_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupportTicket> $supportTickets
 * @property-read int|null $support_tickets_count
 * @property-read \App\Models\Admin|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductVariant> $variants
 * @property-read int|null $variants_count
 * @property-read \App\Models\Vendor|null $vendor
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WishlistItem> $wishlistItems
 * @property-read int|null $wishlist_items_count
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDimensions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereReturnPolicy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWarrantyInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $product_id
 * @property int $category_id
 * @property int $is_primary_category
 * @property int|null $added_by
 * @property string $added_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Product $product
 * @method static \Database\Factories\ProductCategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereAddedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereIsPrimaryCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductCategory withoutTrashed()
 */
	class ProductCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property string $image_url
 * @property string $alt_text
 * @property string $image_type
 * @property bool $is_primary
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon $uploaded_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Admin|null $uploadedBy
 * @method static \Database\Factories\ProductImageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductImage withoutTrashed()
 */
	class ProductImage extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $product_id
 * @property string $sku
 * @property string $name
 * @property numeric $price_adjustment
 * @property array<array-key, mixed> $attributes
 * @property string|null $image_url
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $cartItems
 * @property-read int|null $cart_items_count
 * @property-read \App\Models\Inventory|null $inventory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PriceHistory> $priceHistory
 * @property-read int|null $price_history_count
 * @property-read \App\Models\Product $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WishlistItem> $wishlistItems
 * @property-read int|null $wishlist_items_count
 * @method static \Database\Factories\ProductVariantFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereAttributes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePriceAdjustment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant withoutTrashed()
 */
	class ProductVariant extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $code
 * @property string $discount_type
 * @property numeric $discount_value
 * @property numeric|null $min_order_amount
 * @property int|null $max_uses
 * @property array<array-key, mixed>|null $target_audience
 * @property \Illuminate\Support\Carbon $valid_from
 * @property \Illuminate\Support\Carbon $valid_until
 * @property bool $is_active
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static \Database\Factories\PromoCodeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereMaxUses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereMinOrderAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereTargetAudience($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode whereValidUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PromoCode withoutTrashed()
 */
	class PromoCode extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $rating
 * @property string $comment
 * @property float|null $sentiment_score
 * @property int $helpful_count
 * @property \Illuminate\Support\Carbon $created_at
 * @property string $moderation_status
 * @property int|null $moderated_by
 * @property \Illuminate\Support\Carbon|null $moderated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $moderatedBy
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\ReviewFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereHelpfulCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereModeratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereModeratedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereModerationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereSentimentScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Review withoutTrashed()
 */
	class Review extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $order_id
 * @property string $tracking_number
 * @property string|null $origin_country
 * @property string|null $destination_country
 * @property string|null $destination_region
 * @property string|null $destination_zip
 * @property numeric|null $weight
 * @property string|null $shipping_zone
 * @property string $status
 * @property numeric $actual_cost
 * @property string $shipping_method
 * @property string $service_level
 * @property numeric $base_cost
 * @property numeric $per_item_cost
 * @property numeric $per_weight_unit_cost
 * @property int|null $delivery_time_days
 * @property \Illuminate\Support\Carbon $shipped_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $createdBy
 * @property-read \App\Models\Order $order
 * @property-read \App\Models\Admin|null $updatedBy
 * @method static \Database\Factories\ShipmentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereActualCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereBaseCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDeliveryTimeDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDestinationCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDestinationRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereDestinationZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereOriginCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment wherePerItemCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment wherePerWeightUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereServiceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereShippingZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shipment withoutTrashed()
 */
	class Shipment extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscriber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscriber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subscriber query()
 */
	class Subscriber extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $subject
 * @property string $status
 * @property string $priority
 * @property string $department
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int|null $assigned_to
 * @property int|null $order_id
 * @property int|null $product_id
 * @property int|null $resolution_time Time in seconds to resolve
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $assignedAdmin
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\SupportTicketFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereResolutionTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket withoutTrashed()
 */
	class SupportTicket extends \Eloquent {}
}

namespace App\Models{
/**
 * User model representing the users table.
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static create(array $attributes = [])
 * @method static static find($id, $columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static|null first($columns = ['*'])
 * @method static static|null firstOrFail($columns = ['*'])
 * @method bool update(array $attributes = [], array $options = [])
 * @method bool save(array $options = [])
 * @method bool delete()
 * @method static bool insert(array $values)
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash Argon2 hash
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $avatar_url
 * @property string|null $phone_number
 * @property string|null $street
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property string|null $two_factor_secret
 * @property \Illuminate\Support\Carbon|null $last_password_change
 * @property int $failed_login_count
 * @property string $account_status
 * @property \Illuminate\Support\Carbon|null $last_login
 * @property bool $is_verified
 * @property string|null $encrypted_recovery_email
 * @property bool $two_factor_verified
 * @property string $two_factor_method
 * @property array<array-key, mixed>|null $backup_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_enabled_at
 * @property \Illuminate\Support\Carbon|null $two_factor_expires_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $cart
 * @property-read int|null $cart_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \App\Models\UserPreference|null $preferences
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SupportTicket> $supportTickets
 * @property-read int|null $support_tickets_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Wishlist> $wishlists
 * @property-read int|null $wishlists_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccountStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBackupCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEncryptedRecoveryEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFailedLoginCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastPasswordChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePasswordHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorEnabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $language
 * @property string $currency
 * @property string $theme_preference
 * @property array<array-key, mixed>|null $notification_preferences JSON object with notification preferences
 * @property bool $ai_interaction_enabled
 * @property bool $chat_history_enabled
 * @property \Illuminate\Support\Carbon|null $last_interaction_date
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserPreferenceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereAiInteractionEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereChatHistoryEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereLastInteractionDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereNotificationPreferences($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereThemePreference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserPreference withoutTrashed()
 */
	class UserPreference extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property string $name
 * @property string $payment_terms
 * @property string $contact_email
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $contact_name
 * @property string|null $contact_phone
 * @property string|null $address
 * @property string|null $website
 * @property string $status
 * @property int|null $managed_by
 * @property string|null $tax_id
 * @property numeric|null $rating
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Admin|null $managedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Database\Factories\VendorFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereManagedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor wherePaymentTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Vendor withoutTrashed()
 */
	class Vendor extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $wishlist_privacy
 * @property string $created_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WishlistItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\WishlistFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist whereWishlistPrivacy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wishlist withoutTrashed()
 */
	class Wishlist extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $wishlist_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property string $added_at Timestamp when item was added
 * @property string $priority Priority level of the wishlist item
 * @property string|null $notes Optional user notes about the item
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Wishlist $wishlist
 * @method static \Database\Factories\WishlistItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereAddedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem whereWishlistId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WishlistItem withoutTrashed()
 */
	class WishlistItem extends \Eloquent {}
}

