<?php


class Documentation
{
    public function document()
	{
		$documentation = [
            'endpoints' => [
                [
                    'path' => '/constructionStages',
                    'method' => 'GET',
                    'description' => 'Retrieve all construction stages',
                    'function' => 'getAllConstructionStages'
                ],
                [
                    'path' => '/constructionStages/{id}',
                    'method' => 'GET',
                    'description' => 'Retrieve a single construction stage by ID',
                    'function' => 'getSingleConstructionStage'
                ],
                [
                    'path' => '/constructionStages',
                    'method' => 'POST',
                    'description' => 'Create a new construction stage',
                    'function' => 'createConstructionStage'
                ],
                [
                    'path' => '/constructionStages/{id}',
                    'method' => 'PATCH',
                    'description' => 'Update an existing construction stage by ID',
                    'function' => 'updateConstructionStage'
                ],
                [
                    'path' => '/constructionStages/{id}',
                    'method' => 'DELETE',
                    'description' => 'Delete a construction stage by ID',
                    'function' => 'deleteConstructionStage'
                ],
            ]
        ]; 
        header('Content-Type: application/json');
        echo json_encode($documentation, JSON_PRETTY_PRINT);
	}
}