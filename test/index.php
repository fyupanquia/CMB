<?php

require_once(__DIR__."/../CMB.php"); // require library
// Parameters to database connection, don't change the variable's name
$DB_SERVER = "localhost";
$DB_USER = "root";
$DB_PASSWORD = "";
$DB_NAME = "cmb";
/**
 * Set default connection, if you don't send the object PDO to any method.
 */
new CMB(compact("DB_SERVER","DB_USER","DB_PASSWORD","DB_NAME"));
//CMB::setConnection(compact("DB_SERVER","DB_USER","DB_PASSWORD","DB_NAME")); // It is the same to the line before

// Check if it is connected to the data base
if (CMB::isConnected()) {
	// Get another database connection (this won't be assigned  tod the default databse connection)
	$DB_NAME = "cmb2";
	$pdo = CMB::getConnection(compact("DB_SERVER","DB_USER","DB_PASSWORD","DB_NAME"));

	// Set strict mode
	CMB::setStrict(true);
	// Get default database connection
	$default = CMB::getDefaultConnection();
	// You can execute a sql sentence directly
	/* $exec = CMB::exec(["sql"=>"INSERT INTO users SET name='Mario';"]); */
	// You can break up the sql sentence by fields
	$exec = CMB::exec([
						"sql"=>"INSERT INTO users SET name=:name;",
						"fields"=>["name"=>"Mario"]
						]);
	// If you want you choose another database connection ...
	$exec = CMB::exec([
						"sql"=>"INSERT INTO users SET name=:name;",
						"fields"=>["name"=>"Mario"],
						"pdo"=>$pdo
						]);
	// And also you can execute multiple queries (Dont forget the ';' for each sentence and be careful to the capital letters)
	$exec = CMB::exec([
						"sql"=>"INSERT INTO users SET name=:name;INSERT INTO users SET name=:name2;UPDATE users SET name=:name3 WHERE Id=:id;",
						"fields"=>["name"=>"Frank", "name2"=>"", "name3"=>"Luigi", "Id"=>10],
						"pdo"=>$pdo
						]);
	// And also you can execute multiple queries transnationally (be careful to the capital letters)
	$exec = CMB::execT([
		"execs"=>[
			[
				"sql"=>"INSERT INTO users SET name=:name",
				"fields"=>[
					"name"=>"Boney"
				]
			]
		],
		"pdo"=>$pdo
	]);
	
	// example data
	// MASSIVE INSERT
	$insert = CMB::insert([ 
					"InsertS"=>[
						[ 
							"users",
							["age","name","state"],
							[
								["age"=>null,"name"=>"Gregorio","state"=>1]
							]
						]
					],
					"PDO"=>$pdo
				]);
	// testing example
	$data = [];
	for ($i=0; $i < 10000; $i++) { 
		$data[] = ["age"=>$i,"name"=>"cmb {$i}", "state"=>1];
	}

	$insert = CMB::insert([ 
					"INSERTS"=>[
						[ 
							"users", 
							["age","name","state"],
							$data
						]
					],
					"PDO"=>$pdo
				]
				// Also you can choose a previus version
				//,2 insertMassive
				//,1 insertMassiveT
				//,3 insertMassiveTv2 by default
				);

	// MASSIVE DELETE
	$delete = CMB::delete([ 
						"DELETES"=>[
							["users",[ ["Id"=>10,"state"=>1] ]],
						],
						"PDO"=>$pdo
					]);
	// testing example
	$data = [];
	for ($i=0; $i < 10000; $i++) { 
		$data[] = ["Id"=>"{$i}","state"=>1];
	}

	$delete = CMB::delete([ 
					"DELETES"=>[
						["users",$data],
					],
					"PDO"=>$pdo
				]
				// Also you can choose a previus version
				//,2 deleteMassive
				//,1 deleteMassiveT
				//,3 deleteMassiveTv2 by default
				);
	// MASSIVE UPDATE
	$update = CMB::update([
					"UPDATES"=>[
						["users", 
							[
								[["name"=>"RYU"],["id"=>10000]]
							] 
						],
					],
					"PDO"=>$pdo
				]);
	// testing data
	$data = [];
	for ($i=0; $i <= 10000 ; $i++) { 
		$data[]  = [ ["age"=>"{$i}"], ["id"=>"$i"] ];
	}
	$update = CMB::update([
					"UPDATES"=>[
						["users", $data],
					],
					"PDO"=>$pdo
				]
				// Also you can choose a previus version
				//,2 updateMassive
				//,1 updateMassiveT
				//,3 updateMassiveTv2 by default
			);
	// Multiple transactional
	$iud = CMB::uid([
		"deletes"=>[ 
						[
							"users",
							[ ["Id"=>"1006"] ]
						],
					],
		"updates"=>[ 
						[
							"users",
							[ 
								[ ["name"=>"Mario"],["Id"=>"10015"] ] ,
							]
						],
					],
		"inserts"=>[
						[
							"users",
							["name","age"],
							[ ["name"=>"Yoshi","age"=>"18"] ]
						],
					],
		"pdo"=>$pdo
	]);

	var_dump($iud);
	var_dump(CMB::getLastErrorMessages());
} else {
	echo CMB::getLastErrorMessage(); // returns the last error string message
	// var_dump(CMB::getLastErrorMessages()); // returns all error messages
}