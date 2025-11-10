# Vehicle Management System

System zarządzania pojazdami - typ pojazdu, marka, model, rok.

## Quick Overview

- **5 Vehicle Types** (seeded): city_car, small_car, medium_car, large_car, delivery_van
- **Dynamic Brands & Models** (admin-managed via Filament)
- **Many-to-Many** relation: Vehicle Type ↔ Car Model (pivot table)
- **Customer Declaration** - typ pojazdu to deklaracja klienta, nie wymuszamy validacji

## Database Schema

```sql
vehicle_types (5 seeded types)
car_brands (admin-managed)
car_models (admin-managed)
vehicle_type_car_model (pivot)
appointments (vehicle_type_id, car_brand_id, car_model_id, vehicle_year)
```

## Booking Integration

**Booking Wizard - Step 3:**
1. Customer selects Vehicle Type (card UI)
2. Selects Brand from dropdown (all brands - NO filtering by type)
3. Selects Model from dropdown (all models for selected brand - NO filtering by type)
4. Selects Year (1990-2025)

**API Endpoints:**
```
GET /api/vehicle-types  → All active types
GET /api/car-brands     → All active brands
GET /api/car-models?car_brand_id={id} → Models for brand
GET /api/vehicle-years  → [2025...1990]
```

## Filament Resources

### VehicleTypeResource
- Edit only (no create - seeded data)
- Fields: name, slug, examples, sort_order, is_active

### CarBrandResource
- Full CRUD
- Status: pending/active/inactive
- Bulk approve action

### CarModelResource
- Full CRUD with vehicle type assignment (checkboxes)
- Belongs to CarBrand
- Many-to-many with VehicleTypes via pivot

## Troubleshooting

### API 403 Forbidden

**Problem:** Vehicle API blocked for regular users

**Fix:** Routes must use `auth` middleware only (not `role:super-admin`)

```php
// ✅ Correct
Route::middleware(['auth'])->group(function () {
    Route::get('/api/vehicle-types', [VehicleDataController::class, 'vehicleTypes']);
});
```

### Models Empty After Brand Selection

**Check:** Models must be assigned to vehicle types via pivot table in Filament admin.

## Quick Commands

```bash
# Seed vehicle types
php artisan db:seed --class=VehicleTypeSeeder

# Add brand via Tinker
php artisan tinker
>>> CarBrand::create(['name' => 'Toyota', 'slug' => 'toyota', 'status' => 'active']);
>>> $model = CarModel::create(['car_brand_id' => 1, 'name' => 'Corolla', 'slug' => 'corolla']);
>>> $model->vehicleTypes()->attach([2, 3]); // Assign to small_car + medium_car
```

## See Also

- [Booking System](../booking-system/README.md) - Wizard integration
- Main docs: CLAUDE.md (lines 964-1325)
