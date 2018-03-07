<?php
/**
 * @package persistence
 */
Class StreamHelper{
	private $session;
	
	public function __construct($session){
		$this->session = $session;
	}
	
	public function getReferenceKeyFromObject($entity, $autoGen=false){
		if(!is_object($entity))
			throw new Exception("Only object type entity can be stored");
			
		$className = get_class($entity);
		$key = $entity->get($entity->dao()->id());
		if($key == null && $entity->dao()->generatedValueStrategy() == "auto" && $autoGen){
			$key = $this->getGUID();
			$entity->set($entity->dao()->id(), $key);
		}
		if(is_null($key))
			throw new Exception("Key property has not been set yet in ".$className." class");
		$key = ((string)$key);
				
		return $className.":".$key;
	}

	public function getKeyFromObject($entity, $autoGen=false){
		if(!is_object($entity))
			throw new Exception("Only object type entity can be stored");
			
		$className = get_class($entity);
		$key = $entity->get($entity->dao()->id());
		if($key == null && $entity->dao()->generatedValueStrategy() == "auto" && $autoGen){
			$key = $this->getGUID();
			$entity->set($entity->dao()->id(), $key);
		}
		if(is_null($key))
			throw new Exception("Key property has not been set yet in ".$className." class");
			
		$key = ((string)$key);
		return $key;
	}
	
	public function getGUID() {
		$ip = "127001";
		$unknown = 'unknown';
		if ( isset($_SERVER['HTTP_X_FORWARDED_FOR'])
		&& $_SERVER['HTTP_X_FORWARDED_FOR']
		&& strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
		$unknown) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} 
		elseif ( isset($_SERVER['REMOTE_ADDR'])
		&& $_SERVER['REMOTE_ADDR'] &&
		strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		$ip = str_replace(".","", $ip);
		$ip = str_replace(",","", $ip);
		$ip = trim($ip);
		return uniqid($ip);
	}


	private function Object2Array($entity){
        if(!is_object($entity))
			throw new Exception("Only object type entity can be stored");
			
		$propertyList = array();
		$className = get_class($entity);
		$entityDao = $entity->dao();

        //by simple properties
        $propertyNames=$entityDao->propertiesBySimple();
        foreach($propertyNames as $propertyName){
            $propertyValue=$entity->get($propertyName);
            $propertyList[$propertyName]=$propertyValue;
        }

		//by reference properties 
		$propertyNames = array_merge($entityDao->propertiesByLazyLoad(), $entityDao->propertiesByReference());
		foreach($propertyNames as $propertyName){
			$propertyValue = $entity->get($propertyName);
			//the object property, which refer to other object
			if(is_object($propertyValue)){	
				$propertyList[$propertyName] = $this->getReferenceKeyFromObject($propertyValue);
			}
			//the array property, whose element is a primary value or refers to other object
			elseif(is_array($propertyValue)){
				$keyArray = array(); 
                $className=null;
				foreach($propertyValue as $listKey=>$listValue){
					if(!is_object($listValue)){
						throw new Exception("By reference property ".$property->getName()." should be an object or an array of object, but it is a ".gettype($value));
					}else{
						$keyArray[$listKey] = $this->getKeyFromObject($listValue);

                        if($className==null){
                             $className=get_class($listValue);
                        }
					}
				}

				$arrayjson=array();
                array_push($arrayjson,$className);
                array_push($arrayjson,$keyArray);

				$propertyList[$propertyName] = $arrayjson;
			}
			elseif(is_null($propertyValue)){
				//skip that...
			}
			else{
				throw new Exception("By reference property ".$className.":".$propertyName." should be an object or an array of object");
			}
		}
		
		
		
		//by value properties
		$propertyNames = $entityDao->propertiesByValue();
		foreach($propertyNames as $propertyName){
			$propertyValue = $entity->get($propertyName);
			//the object property, which refer to other object
			if(is_object($propertyValue)){	
                $className=get_class($propertyValue);
                $valueArray= $this->Object2Array($propertyValue);

                $arrayjson=array();
                array_push($arrayjson,$className);
                array_push($arrayjson,$valueArray);

				$propertyList[$propertyName] = $arrayjson;
			}
			//the array property, whose element is a primary value or refers to other object
			elseif(is_array($propertyValue)){
                $valueObjectList=array();
                $className=null;
                foreach($propertyValue as $key=>$value){
                    if(is_object($value)){
				        $valueObjectList[$key] = $this->Object2Array($value);
                        if($className==null){
                             $className=get_class($value);
                        }
                    }else{
				        throw new Exception("by value properties should be an object in array.");
                    }
                }

                $arrayjson=array();
                array_push($arrayjson,$className);
                array_push($arrayjson,$valueObjectList);

			    $propertyList[$propertyName] = $arrayjson;
				
			}
			elseif(is_null($propertyValue)){
				//skip that...
			}
			else{
				throw new Exception("By reference property ".$className.":".$propertyName." should be an object or an array of object");
			}
		}
		
		
		//the "key" property
		$keyPropertyname =  $entityDao->id();
		if(is_int($entity->get($keyPropertyname)) || is_string($entity->get($keyPropertyname)))
            $key = (string)($entity->get($keyPropertyname));
		else
			throw new Exception("Key property ".$keyPropertyname." should be a int or a string");
		
		
		if(is_null($key))
			throw new Exception("Key property has not been set yet in ".$className." class");
	
        //$propertyList["className"] = $className;
        //$propertyList["Key"] = $key;
		return $propertyList;

    }
    
    public function Object2Json($entity){

        $valueArray=$this->Object2Array($entity);
        $propertyListString=json_encode($valueArray);
        $propertyListString = str_replace("\\", "\\\\", $propertyListString);
        $className=get_class($entity);
        $key=$this->getKeyFromObject($entity,true);
    	$returnList = array();
		$returnList["ClassName"] = $className;
		$returnList["Key"] = $key;
		$returnList["PropertyListString"] = $propertyListString;


        $indexAttr = array();
		$indexValue = array();
		$indexType = array();

        $returnList["indexAttr"] = $indexAttr;
		$returnList["indexValue"] = $indexValue;
		$returnList["indexType"] = $indexType;

		
		return $returnList;


    }

	public function cascadePut($entity){
		$entityDao = $entity->dao();
		$cascadeProperties = $entityDao->propertiesByCascadeSave();
		$cascadeClassMap = array();
		foreach($cascadeProperties as $cascadeProperty){
			$cascadeValue = $entity->get($cascadeProperty);
			if(!is_null($cascadeValue)){
                if(is_object($cascadeValue)){
				    $this->session->put($cascadeValue);
                }elseif(is_array($cascadeValue)){
                    foreach($cascadeValue as $key=>$value){
                        if(is_object($value)){
                            $this->session->put($value);
                        }else{
                            throw new Excepton("cascadePut should be an object or array field of reference && cascade(save|all).");
                        }

                    }
                }
			}
		}
		
			
	}
	
	public function cascadeRemove($entity){
		$entityDao = $entity->dao();
		$cascadeProperties = $entityDao->propertiesByCascadeRemove();
		foreach($cascadeProperties as $cascadeProperty){
			$cascadeValue = $entity->get($cascadeProperty);
			if(is_null($cascadeValue))
				continue;
			else{
				$this->session->remove($cascadeValue);
			}
		}
	}
	
    private function Array2Object($className,$propertyList){
        $entity = new $className();
		$entityDao = $entity->dao();
		//$propertyNames = $entityDao->properties();

        // simple properties
        $propertyNames=$entityDao->propertiesBySimple();
        foreach($propertyNames as $propertyName){
            if( isset($propertyList[$propertyName]) ){

                $entity->set($propertyName,$propertyList[$propertyName]);
            }
        }


		//lazy load properties
		$propertyNames = $entityDao->propertiesByLazyLoad();
		foreach($propertyNames as $propertyName){
			if(isset($propertyList[$propertyName]))
				$propertyValue = $propertyList[$propertyName];
			else
				continue;
				
			if(is_array($propertyValue)){
				$valueArray = array(); 
				foreach($propertyValue as $key => $value){
					$valueArray[$key] = $value;
				}	
				$attrs[$propertyName] = $valueArray;
			}
			else{
				$attrs[$propertyName] = $propertyValue;
			}
				
		}
		
		//by reference properties but not lazy load
		$propertyNames = $entityDao->propertiesByReference();
		foreach($propertyNames as $propertyName){
			if(isset($propertyList[$propertyName])){
                $propertyValue = $propertyList[$propertyName];
                if(is_string($propertyValue)){// Object reference  "className:key"
                     $entity->set($propertyName, $this->keyClassSearch($propertyValue));   
                }else{// array object reference

                    $inclassName=array_shift($propertyValue);
                    $inValue=array_shift($propertyValue);
                    if($inValue==null){continue;}

                        $inarray=array();
                        foreach($inValue as $k=>$v){
                           $inarray[$k]=$this->keyClassSearch($inclassName.":".$v); 
                        }

                        $entity->set($propertyName,$inarray);
                }
            }else{
				continue;
			}	

			if(is_array($propertyValue) && !empty($propertyValue)){
                $classname=array_shift($propertyValue);
                $innerArrayValue=array_shift($propertyValue);
				$valueArray = array(); 
				foreach($innerArrayValue as $key => $value){
					$valueArray[$key] = $this->keyClassSearch($classname.":".$value);
				}
				$attrs[$propertyName] = $valueArray;
			}else{
				$attrs[$propertyName] = $this->keyClassSearch($propertyValue);
			}	
		}
		//by value properties
		$propertyNames = $entityDao->propertiesByValue();
		foreach($propertyNames as $propertyName){
			if(isset($propertyList[$propertyName])){
				$propertyValue = $propertyList[$propertyName];
                $inclassName=array_shift($propertyValue);
                $inValue=array_shift($propertyValue);
                if($inValue==null){continue;}
                if(isset($inValue["className"])){
                    $inentity=$this->Array2Object($inclassName,$inValue);
                    $entity->set($propertyName,$inentity);

                }else{
                    $inarray=array();
                    foreach($inValue as $k=>$v){
                       $inarray[$k]=$this->Array2Object($inclassName,$v); 
                    }

                    $entity->set($propertyName,$inarray);
                }

            }else
				continue;
		}
        return $entity;
    }
    public function Json2Object($className, $propertyListString){
        
		if($propertyListString == ""){
			return null;
		}	
		$propertyList = json_decode($propertyListString,TRUE);

		if($propertyList == null){
			throw new Exception("Can not decode json string ".$propertyListString);
		}

        $entity=$this->Array2Object($className,$propertyList);

		foreach($propertyList as $key=>$value){
			if(is_string($value)){
				$propertyList[$key] = $value;
            }
		}
		
		$entity->setSaved(true);
		return $entity;
	}
	
	public function keyClassSearch($keyClass)
	{
		if(is_string($keyClass))
		{
			if(strpos($keyClass, ':') === false){
			return null;
			}
			$keyClassSplit = explode(":", $keyClass);
			$className = $keyClassSplit[0];
			$key = ($keyClassSplit[1]);
			$value= $this->session->get($className, $key);
            return $value;
		}
		elseif(is_array($keyClass))
		{
			$keyArray = array();
			$keyPropertyMap = array();
			$propertyEntityMap = array();
			$results = array();
			$className = "";
			foreach($keyClass as $property => $keyClassString)
			{
				if(is_null($property)
					|| is_null($keyClassString)){
					continue;
				}
				if(!is_string($keyClassString)
					|| strpos($keyClassString, ':') === false){
					$results[$property] = $keyClassString;
					continue;
				}
				$keyClassSplit = explode(":", $keyClassString);
				//assume that all the class name are the same
				$className = $keyClassSplit[0];
				$key = ($keyClassSplit[1]);
				$keyPropertyMap[$key] = $property;
				array_push($keyArray, $key);
			}
			if($className != "")
			{
				$keyEntityMap = $this->session->get($className, $keyArray);
				foreach($keyEntityMap as $key => $value)
				{
					$results[$keyPropertyMap[$key]] = $value;
				}
			}
			return $results;
		}
		else
		{
			return null;
		}
	}
}
 
?>
