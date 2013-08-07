# db.php - PDO wrapper

## select_objects($sql, $parameters)

Return a simple type and object mapped set of records 
		
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
			
Returns:
		
			[{
				id: 1234,
				name: {
					'first': "Sideshow",
					'last': "Bob"
				},
				other: "some value"
			}, ...]
