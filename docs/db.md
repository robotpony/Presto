# db.php - PDO wrapper

## select_objects($sql, $parameters)

Uses column aliases and type designation to generate object hierarchy.

		Features:

			* simple key format `any.number.of.subkeys:optional_type`
			* order of columns is not important
			* allows values to be type cast

		Not supported:

			* combining rows into sub-objects

		Example:

			SELECT
				SomeID AS `id:int`,
				FirstName AS `name.first`,
				LastName AS `name.last`,
				AnotherColumn AS `other`
			FROM SomeTable


			[{
				id: 1234,
				name: {
					'first': "Sideshow",
					'last': "Bob"
				},
				other: "some value"
			}, ...]

## bind_parameters

		Generates a PDO bound parameterized array.
		
		Pass this an array of keys and values that you want to use in your DB query.
		generate_params() will return you a valid PDO array to use with 
		your INSERT and UPDATE statements.
		
		View the test harness for this here: https://gist.github.com/ngallagher87/6717925
		
		Supported types:
		=================
			PARAM_BOOL
			PARAM_NULL
			PARAM_INT
			PARAM_STR
		
		Unsupported types:
		=================
			PARAM_LOB
			PARAM_INPUT_OUTPUT
			PARAM_STMT (No drivers support this anyways)
		
		Note:
		
			If you need to use one of these unsupported types, you'll have to
			generate the params by hand.
		
		Example:
		========
		
			$sql = <<<SQL
				INSERT INTO Days (Day, DayNumber, isHoliday)
				VALUES (:day, :dayNumber, :isHoliday);
			SQL;
			
			$values = array(
				'day' => 'tuesday', 
				'dayNumber' => 2, 
				'isHoliday' => true
			);
			$this->db->insert($sql, $params);
