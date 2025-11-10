# Booking System

Multi-step wizard for appointment booking.

## Overview

- **4-Step Wizard** (vanilla JavaScript, no framework)
- Service selection → Date/Time → Vehicle & Location → Confirmation
- Calendar integration: Guava Calendar v1.14.2
- Queue-based appointment creation

## Wizard Steps

**Step 1: Service Selection**
- Browse services with details
- Click "Book Now" → Step 2

**Step 2: Date & Time**
- Calendar date picker
- Available time slots (API call)
- 24-hour advance booking requirement

**Step 3: Vehicle & Location**
- Vehicle type cards (5 types)
- Brand/Model/Year dropdowns
- Google Maps Autocomplete for location

**Step 4: Confirmation**
- Review all details
- Submit → Queue job → Email confirmation

## Database Schema

```sql
appointments (
    id, customer_id, staff_id, service_id,
    scheduled_at, duration_minutes, status,
    vehicle_type_id, car_brand_id, car_model_id, vehicle_year,
    location_address, location_latitude, location_longitude,
    location_place_id, location_components,
    reminder_24h_sent_at, reminder_2h_sent_at, followup_sent_at
)
```

## Statuses

- `pending` - Awaiting confirmation
- `confirmed` - Confirmed by staff
- `in_progress` - Service in progress
- `completed` - Finished
- `cancelled` - Cancelled by customer/staff

## API Endpoints

```
GET /api/services
GET /api/available-slots?service_id={id}&date={YYYY-MM-DD}
POST /appointments (create new appointment)
```

## See Also

- [Vehicle Management](../vehicle-management/README.md) - Step 3 vehicle selection
- [Google Maps](../google-maps/README.md) - Step 3 location capture
- [Email System](../email-system/README.md) - Confirmation emails
