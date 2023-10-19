<?php

function validateConstructionStage($data)
{
    // Check 'name'
    if (isset($data->name) && strlen($data->name) > 255) {
        throw new Exception("Name must be a maximum of 255 characters.");
    }

    // Check 'startDate'
    if (isset($data->startDate)) {
        $startDate = date_create_from_format(DateTime::ATOM, $data->startDate);
        if (!$startDate) {
            throw new Exception("Invalid start_date format. It should be in the ISO8601 format (e.g., 2022-12-31T14:59:00Z).");
        }
    }
  

    // Check 'endDate'
    if (isset($data->endDate)) {
        $endDate = date_create_from_format(DateTime::ATOM, $data->endDate);
        if (!$endDate) {
            throw new Exception("Invalid end_date format. It should be in the ISO8601 format (e.g., 2022-12-31T14:59:00Z).");
        } elseif (isset($startDate) && $startDate >= $endDate) {
            throw new Exception("end_date should be later than start_date.");
        }
    }

    // Check 'durationUnit'
    $validDurationUnits = ['HOURS', 'DAYS', 'WEEKS'];
    if (isset($data->durationUnit) && !in_array($data->durationUnit, $validDurationUnits)) {
        throw new Exception("Invalid durationUnit. It should be one of: " . implode(', ', $validDurationUnits));
    }

    // Check 'color'
    if (isset($data->color) && !preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $data->color)) {
        throw new Exception("Invalid color. It should be a valid HEX color code (e.g., #FF0000).");
    }

    // Check 'externalId'
    if (isset($data->externalId) && strlen($data->externalId) > 255) {
        throw new Exception("externalId must be a maximum of 255 characters.");
    }

    // Check 'status'
    $validStatusValues = ['NEW', 'PLANNED', 'DELETED'];
    if (isset($data->status) && !in_array($data->status, $validStatusValues)) {
        throw new Exception("Invalid status. It should be one of: " . implode(', ', $validStatusValues));
    }
}