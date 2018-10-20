# CMB CLASS
- **Description** : A easy and fast way to execute trasactional and no transactional CRUD queries.

### getConnection
- **Description** : Create a new pdo object, it don't be assigned to the static property $connection
- **Params** : ---
- **Return** : PDO object or false if there is an error.

### getDefaultConnection
- **Description** : Get connection that is used by CMB
- **Params** : ---
- **Return** : PDO

### setConnection
- **Description** : set a new pdo connection 
- **Params** : ---
- **Return** : ---

### isConnected
- **Description** : Eval if PDO object is correct
- **Params** : ---
- **Return** : Boolean, true if it is right or false if not

### getPDO
- **Description** : Get default PDO object
- **Params** : ---
- **Return** : PDO object if is set , null if not

### getLastErrorMessage
- **Description** : Catch exceptions or query error details 
- **Params** : ---
- **Return** : String

### getLastErrorMessage
- **Description** : Catch exceptions or query error details 
- **Params** : ---
- **Return** : Array


### setStrict
- **Description** : Set strict mode
- **Params** : $b Boolean
- **Return** : ---

### setStrict
- **Description** : Get strict mode
- **Params** : ---
- **Return** : Boolean

### exec
- **Description** : Execute a any query (INSERT,UPDATE,DELETE,DROP,ALTER,etc) NOT TRANSACTIONAL, you can execute multiple queries (separate them by semicolon). If one of the sentences is wrong the following won't be executed and also it won't be catched by CMB if the wrong senteces is not the first.
- **Params** : $params (Array(
						"sql"=>"",
						"fields"=>["field"=>"value"], (*)
						"pdo"=>PDO Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::exec([
						"sql"=>"INSERT INTO users SET name=:name;UPDATE users SET ge=:age WHERE Id=:Id;",
						"fields"=>["name"=>"fyupanquia", "age"=>11, "Id"=>10],
						"pdo"=>null
					])
			```
- Observation: 
> Not recommendable execute too much sentences

### execT
- **Description** : Execute multiple any query (INSERT,UPDATE,DELETE,DROP,ALTER,etc) TRANSACTIONAL, you can execute multiple queries (separate them by array indexes), If one of them is wrong any sentences will be commit.
- **Params** : $params (Array(
						"execs"=>[ 
									[
										"sql"=>"",
										"fields"=>[] (*)
									],
									[
										"sql"=>"",
										"fields"=>[] (*)
									]
								],
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
				```
				CMB::execT(
									[ 	
										"execs"=>[
													["sql"=>"INSERT INTO users SET fullname=:fullname;","fields"=>["fullname"=>"frank"]],
													["sql"=>"INSERT INTO users SET fullname=:fullname;","fields"=>["fullname"=>"frank2"]],
													["sql"=>"INSERT INTO users SET fullname=:fullname;","fields"=>["fullname"=>"frank3"]]
												]
									]
								);
				```
### insert
- **Description** : Execute multiple INSERT sentences
- **Params** : $params (Array(
						"inserts"=>[ 
										[
											"table",
											["field1","field2"],
											[ ["field1"=>"value","field2"=>"value"] , ...]
										], ...
									],
						"pdo"=>Object (*),
						Integer // By default 3
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
				```
					CMB::insert($params, 1)
					CMB::insert($params, 2)
					CMB::insert($params, 3) // By default
				```
### insertMassive
- **Description** : Execute multiple INSERT sentences NOT TRANSACTIONAL
- **Params** : $params (Array(
						"inserts"=>[ 
										[
											"table",
											["field1","field2"],
											[ ["field1"=>"value","field2"=>"value"] , ...]
										], ...
									],
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
				```
					CMB::insertMassive([
								"inserts"=>[
									[
										"users",
										["id","age","fullname","state"],
										[ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"], ["id"=>2,"age"=>1,"fullname"=>"fullname","state"=>"1"] ] 
									],
									[
										"users2",
										["id","age","fullname","state"],
										[ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"] ]
									]
								]);
				```
### insertMassiveT
- **Description** : Execute multiple INSERT sentences TRANSACTIONAL
- **Params** : $params (Array(
						"inserts"=>[ 
										[
											"table",
											["field1","field2"],
											[ ["field1"=>"value","field2"=>"value"] , ...]
										], ...
									],
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::insertMassiveT([
								"inserts"=>[
									[
										"users",
										["id","age","fullname","state"],
										[ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"], ["id"=>2,"age"=>1,"fullname"=>"fullname","state"=>"1"] ] 
									],
									[
										"users2",
										["id","age","fullname","state"],
										[ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"] ]
									]
								]);
			```
### insertMassiveTv2
- **Description** : Execute multiple INSERT sentences TRANSACTIONAL
- **Params** : $params (Array(
						"inserts"=>[ 
										[
											"table",
											["field1","field2"],
											[ ["field1"=>"value","field2"=>"value"] , ...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::insertMassiveTv2([
								"inserts"=>[
									[
										"users",
										["id","age","fullname","state"],
										[ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"], ["id"=>2,"age"=>1,"fullname"=>"fullname","state"=>"1"] ] 
									],
									[
										"users2",
										["id","age","fullname","state"],
										[ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"] ]
									]
								]);
			```
### update
- **Description** : Execute multiple UPDATE sentences
- **Params** : $params (Array(
						"updates"=>[ 
										[
											"table",
											[ [ ["field"=>"value"],["where"=>"value"] ] , ...]
										], ...
									],
						"pdo"=>Object (*),
						Integer // By default 3
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
				```
					CMB::update($params, 1)
					CMB::update($params, 2)
					CMB::update($params, 3) // By default
				```
### updateMassive
- **Description** : Execute multiple UPDATE sentences NOT TRANSACTIONAL
- **Params** : $params (Array(
						"updates"=>[ 
										[
											"table",
											[ [ ["field"=>"value"],["where"=>"value"] ] , ...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
            CMB::updateMassive([
                                "updates"=>[
                                    [
                                        "users",
                                        [ 
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
                                    ],
                                    [
                                        "users2",
                                        [
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
                                    ], ...
                                ]);
			```
### updateMassiveT
- **Description** : Execute multiple UPDATE sentences TRANSACTIONAL
- **Params** : $params (Array(
						"updates"=>[ 
										[
											"table",
											[ [ ["field"=>"value"],["where"=>"value"] ] , ...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
            CMB::updateMassiveT([
                                "updates"=>[
                                    [
                                        "users",
                                        [ 
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
                                    ],
                                    [
                                        "users2",
                                        [
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
                                    ], ...
                                ]);
			```
### updateMassiveTv2
- **Description** : Execute multiple UPDATE sentences TRANSACTIONAL
- **Params** : $params (Array(
						"updates"=>[ 
										[
											"table",
											[ [ ["field"=>"value"],["where"=>"value"] ] , ...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::updateMassiveTv2([
								"updates"=>[
									[
										"users",
										[ 
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
									],
									[
										"users2",
										[
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
									], ...
								]);
			```
### delete
- **Description** : Execute multiple DELETE sentences
- **Params** : $params (Array(
						"deletes"=>[ 
										[
											"table",
											[ ["where"=>"value"] , ["where2"=>"value"],...]
										], ...
									],
						"pdo"=>Object (*),
						Integer // By default 3
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
				```
					CMB::delete($params, 1)
					CMB::delete($params, 2)
					CMB::delete($params, 3) // By default
				```
### deleteMassive
- **Description** : Execute multiple DELETE sentences NOT TRANSACTIONAL
- **Params** : $params (Array(
						"deletes"=>[ 
										[
											"table",
											[ ["where"=>"value"] , ["where2"=>"value"],...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::deleteMassive([
								"deletes"=>[
									[
										"users",
										[ ["id"=>"value1"], ["id"=>"value2"] ] 
									],
									[
										"users2",
										[ ["id|<"=>"value1"], ["id<!="=>"value2"] ]  // WHERE id<"value1" AND id!="value2"
									]
								]);
			```
### deleteMassiveT
- **Description** : Execute multiple DELETE sentences TRANSACTIONAL
- **Params** : $params (Array(
						"deletes"=>[ 
										[
											"table",
											[ ["where"=>"value"] , ["where2"=>"value"],...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::deleteMassiveT([
								"deletes"=>[
									[
										"users",
										[ ["id"=>"value","age"=>"value"], ["id"=>"value2"] ] 
									],
									[
										"users2",
										[ ["id"=>"value","age"=>"value"], ["id"=>"value2"] ] 
									]
								]);
			```
### deleteMassiveTv2
- **Description** : Execute multiple DELETE sentences TRANSACTIONAL
- **Params** : $params (Array(
						"deletes"=>[ 
										[
											"table",
											[ ["where"=>"value"] , ["where2"=>"value"],...]
										], ...
									]
						"pdo"=>Object (*)
						)
				)
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
			```
			CMB::deleteMassiveTv2([
								"deletes"=>[
									[
										"users",
										[ ["id"=>"value","age"=>"value"], ["id"=>"value2"] ] 
									],
									[
										"users2",
										[ ["id"=>"value","age"=>"value"], ["id"=>"value2"] ] 
									]
								]);
			```
### iud
### iudMassiveTv2
- **Description** : Execute multiple INSERT,UPDATE,DELETE sentences TRANSACTIONAL
- **Params** : $params (Array(
                        "deletes"=>[ 
                                        [
                                            "table",
                                            [ ["where"=>"value"] , ["where2"=>"value"],...]
                                        ], ...
                                    ],
                        "updates"=>[ 
                                        [
                                            "table",
                                            [ 
                                                [ ["field"=>"value"],["where"=>"value"] ] , ...
                                            ]
                                        ], ...
                                    ],
                        "inserts"=>[ 
                                        [
                                            "table",
                                            ["field1","field2"],
                                            [ ["field1"=>"value","field2"=>"value"] , ...]
                                        ], ...
                                    ]  
                        "pdo"=>Object (*)
                        )
                )
- **Return** : Boolean, true if all is right, false if not.
- **Example** : 
            ```
            CMB::deleteMassiveTv2([
                                "deletes"=>[
                                    [
                                        "users",
                                        [ ["id"=>"value","age"=>"value"], ["id"=>"value2"] ] 
                                    ],
                                    [
                                        "users2",
                                        [ ["id"=>"value","age"=>"value"], ["id"=>"value2"] ] 
                                    ]
                                ],
                                "updates"=>[
                                    [
                                        "users",
                                        [ 
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
                                    ],
                                    [
                                        "users2",
                                        [
                                            [ ["name"=>"name","age"=>"age"], ["id"=>"value"] ], ...
                                        ]
                                    ], ...
                                ],
                                "inserts"=>[
                                    [
                                        "users",
                                        ["id","age","fullname","state"],
                                        [ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"], ["id"=>2,"age"=>1,"fullname"=>"fullname","state"=>"1"] ] 
                                    ],
                                    [
                                        "users2",
                                        ["id","age","fullname","state"],
                                        [ ["id"=>1,"age"=>1,"fullname"=>"fullname","state"=>"1"] ]
                                    ]
                                ]);
            ```