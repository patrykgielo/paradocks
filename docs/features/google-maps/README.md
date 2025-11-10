# Google Maps Integration

Autocomplete address capture + map display in booking wizard.

## Implementation

**Modern JavaScript API** (NOT Web Components):
- ✅ `google.maps.places.Autocomplete` class
- ✅ `google.maps.importLibrary("places")` 
- ✅ `AdvancedMarkerElement` (latest marker API)
- ✅ Regular `<input>` with Autocomplete attached
- ❌ `<gmp-place-autocomplete>` Web Component (buggy, not production-ready)

## Setup

1. Get API key: https://console.cloud.google.com/google/maps-apis/
2. Enable APIs: Maps JavaScript API + Places API (New)
3. Add to `.env`:
```bash
GOOGLE_MAPS_API_KEY=AIzaSy...
GOOGLE_MAPS_MAP_ID=your_map_id
```

## Booking Wizard Integration

**Step 3:** Customer enters service location
- Autocomplete suggests addresses (Poland only)
- Captures: formatted_address, lat/lng, place_id, address_components
- Map updates with marker at selected location
- Legacy fields auto-filled (street, city, postal_code)

**Database Fields (appointments):**
```sql
location_address VARCHAR(500)
location_latitude DOUBLE(10,8)
location_longitude DOUBLE(11,8)
location_place_id VARCHAR(255)
location_components JSON
```

## Troubleshooting

### Autocomplete Not Working

1. Check API key in `.env`
2. Verify Places API (New) enabled in Google Cloud Console
3. Check browser console for errors
4. Verify HTTP referrer restrictions allow paradocks.local

### Deprecation Warning

```
"google.maps.places.Autocomplete is not available to new customers"
```

**This is misleading!** Only affects NEW Google Cloud accounts after March 1, 2025.

**Our implementation is CORRECT and modern** - ignore warning, use JavaScript API (not Web Components).

## See Also

- CLAUDE.md (lines 547-964) - Full documentation
- Google Maps JS API: https://developers.google.com/maps/documentation/javascript
