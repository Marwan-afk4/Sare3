# Driver API - Get Model Type IDs

## Endpoint
`GET /api/driver/get-model-type-ids`

## Description
Returns car models, car types (with year ranges and individual years), and car categories for driver registration.

## Response Structure

### Car Types with Year Range Support

Each car type now includes year range information and an array of individual years:

```json
{
  "carModels": [...],
  "carTypes": [
    {
      "id": 1,
      "car_model_id": 1,
      "type_name": "Sedan 2020-2025",
      "year_from": 2020,
      "year_to": 2025,
      "year_range": "2020 - 2025",
      "years": [2020, 2021, 2022, 2023, 2024, 2025],
      "description": "Modern sedan models from 2020 to 2025",
      "car_categories": [...],
      "created_at": "2026-01-08T00:00:00.000000Z",
      "updated_at": "2026-01-08T00:00:00.000000Z"
    },
    {
      "id": 2,
      "car_model_id": 2,
      "type_name": "SUV 2018+",
      "year_from": 2018,
      "year_to": null,
      "year_range": "2018+",
      "years": [2018, 2019, 2020, 2021, 2022, 2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030, 2031, 2032, 2033, 2034, 2035, 2036],
      "description": "SUV models from 2018 onwards",
      "car_categories": [...],
      "created_at": "2026-01-08T00:00:00.000000Z",
      "updated_at": "2026-01-08T00:00:00.000000Z"
    },
    {
      "id": 3,
      "car_model_id": 3,
      "type_name": "Hatchback Up to 2022",
      "year_from": null,
      "year_to": 2022,
      "year_range": "Up to 2022",
      "years": [1980, 1981, 1982, ..., 2020, 2021, 2022],
      "description": "Hatchback models up to 2022",
      "car_categories": [...],
      "created_at": "2026-01-08T00:00:00.000000Z",
      "updated_at": "2026-01-08T00:00:00.000000Z"
    },
    {
      "id": 4,
      "car_model_id": 4,
      "type_name": "Classic Car",
      "year_from": null,
      "year_to": null,
      "year_range": "-",
      "years": [],
      "description": "Classic cars with no year restriction",
      "car_categories": [...],
      "created_at": "2026-01-08T00:00:00.000000Z",
      "updated_at": "2026-01-08T00:00:00.000000Z"
    }
  ],
  "carCategories": [...]
}
```

## Year Range Field Explanations

- **year_from**: Starting year (integer or null)
- **year_to**: Ending year (integer or null)  
- **year_range**: Formatted display string with the following formats:
  - `"2020 - 2025"` - Full range (both years specified)
  - `"2020+"` - From year onwards (only year_from specified)
  - `"Up to 2025"` - Up to year (only year_to specified)
  - `"-"` - No year restriction (both null)
- **years**: Array of individual years within the range
  - Full range: `[2020, 2021, 2022, 2023, 2024, 2025]`
  - From year: `[2018, 2019, 2020, ..., 2036]` (up to current year + 10)
  - Up to year: `[1980, 1981, ..., 2022]` (from 1980 or 50 years before)
  - No restriction: `[]` (empty array)

## Years Array Generation Logic

1. **Full Range** (year_from AND year_to): All years from start to end
2. **From Year** (year_from only): From start year to current year + 10
3. **Up to Year** (year_to only): From 1980 (or 50 years before year_to) to end year
4. **No Restriction** (neither): Empty array

## Usage in Mobile App

The mobile app can now:
1. Display year ranges in car type selection
2. Show dropdown/picker with individual years from the `years` array
3. Validate car year selection against available years
4. Filter car types by specific years
5. Show appropriate year information to drivers

## Example Usage

For a car type with range 2020-2026:
```json
{
  "year_from": 2020,
  "year_to": 2026,
  "year_range": "2020 - 2026",
  "years": [2020, 2021, 2022, 2023, 2024, 2025, 2026]
}
```

The mobile app can use the `years` array to populate a year selection dropdown, allowing users to pick any year from 2020 to 2026.

## Backward Compatibility

This update is backward compatible. The response includes all necessary information for both old and new implementations, with the addition of the `years` array for enhanced functionality.
