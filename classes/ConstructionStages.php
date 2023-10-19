<?php
require_once 'validation.php';

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}


	/**
	 * Perform an operation to get all construction stage.
	 *
	 * @return json all construction stages data.
	 * 
	 */

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Perform an operation to get single construction stage.
	 *
	 * @param int $id The ID of the construction stage to get data.
	 * @return json construction stage data.
	 * 
	 */

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Perform an operation to create a new construction stage.
	 *
	 * @param object $data The data object containing the construction stage information.
	 * @return json new created construction stage data.
	 * @throws ExceptionType The method may throw exceptions when validation fails.
	 * @see validateConstructionStage() used to validate all fields.
	 * @see calculateDuration() used to calculate duration.
	 */

	public function post(ConstructionStagesCreate $data)
	{
		try {
			validateConstructionStage($data); 

			$duration = $this->calculateDuration($data->startDate, $data->endDate, $data->durationUnit);

			$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
			$stmt->execute([
				'name' => $data->name,
				'start_date' => $data->startDate,
				'end_date' => $data->endDate,
				'duration' => $duration,
				'durationUnit' => $data->durationUnit,
				'color' => $data->color,
				'externalId' => $data->externalId,
				'status' => $data->status,
			]);
			return $this->getSingle($this->db->lastInsertId());
			
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
	}

	/**
	 * Perform an operation to update existing construction stage.
	 *
	 * @param object $data The data object containing the updated construction stage information.
	 * @param int $id The ID of the construction stage to be updated.
	 * @return json updated construction stage data.
	 * @throws ExceptionType The method may throw exceptions when validation fails.
	 * @see validateConstructionStage() used to validate all fields.
	 */
	public function patch(ConstructionStagesUpdate $data, $id)
	{
		try {
			validateConstructionStage($data); 

			$stmt = $this->db->prepare("
				UPDATE construction_stages
				SET 
					name = COALESCE(:name, name),
					start_date = COALESCE(:start_date, start_date),
					end_date = COALESCE(:end_date, end_date),
					duration = COALESCE(:duration, duration),
					durationUnit = COALESCE(:durationUnit, durationUnit),
					color = COALESCE(:color, color),
					externalId = COALESCE(:externalId, externalId),
					status = COALESCE(:status, status)
				WHERE id = :id
			");
			$stmt->execute([
				'id' => $id,
				'name' => isset($data->name) ? $data->name : null,
				'start_date' => isset($data->startDate) ? $data->startDate : null,
				'end_date' => isset($data->endDate) ? $data->endDate : null,
				'duration' => isset($data->duration) ? $data->duration : null,
				'durationUnit' => isset($data->durationUnit) ? $data->durationUnit : null,
				'color' => isset($data->color) ? $data->color : null,
				'externalId' => isset($data->externalId) ? $data->externalId : null,
				'status' => isset($data->status) ? $data->status : null,
			]);

			return $this->getSingle($id);
			
		} catch (Exception $e) {
			return $e->getMessage();
		}

		$statusValues = ['NEW', 'PLANNED', 'DELETED'];
		if (isset($data->status) && !in_array($data->status, $statusValues)) {
			return "Invalid status. Status must be one of: " . implode(', ', $statusValues);
			//throw new Exception("Invalid status. Status must be one of: " . implode(', ', $statusValues));
		}
		
	}

	/**
	 * Perform an operation to delete construction stage.
	 *
	 * @param int $id The ID of the construction stage to be deleted.
	 * @return string Success or error message.
	 * 
	 */

	public function delete($id)
	{
		$stmt = $this->db->prepare("
			DELETE FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		$affectedRows = $stmt->rowCount();
		if ($affectedRows > 0) {
			return "Construction stage with ID: $id has been deleted successfully.";
		} else {
			return "Construction stage with ID: $id was not found.";
		}
	}

	/**
	 * Perform an operation to calculate duration.
	 *
	 * @param dateTime $start_date Start date of the construction stage to be created.
	 * @param dateTime $end_date End date of the construction stage to be created.
	 * @param string $durationUnit Duration Unit of the construction stage to be created.
	 * @return int duration.
	 * 
	 */

	function calculateDuration($start_date, $end_date, $durationUnit)
	{
		$validDurationUnits = ['HOURS', 'DAYS', 'WEEKS'];
		$duration = null;

		if (!in_array($durationUnit, $validDurationUnits)) {
			$durationUnit = 'DAYS'; // Set default duration unit to DAYS
		}

		if ($end_date !== null) {
			$start = new DateTime($start_date);
			$end = new DateTime($end_date);

			// Calculate the difference in days between start and end dates
			$diff = $end->diff($start)->days;

			switch ($durationUnit) {
				case 'HOURS':
					$duration = $diff * 24; // Convert days to hours
					break;
				case 'WEEKS':
					$duration = $diff * 7; // Convert days to weeks
					break;
				default:
					$duration = $diff;
			}
		}

		return $duration;
	}
}