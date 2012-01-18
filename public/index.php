<?php

define( 'SEP', '#' );

class DataConfig
{
	protected $_datasources = array();
	protected $_document = null;

	public function __construct( SimpleXMLElement $element )
	{
		$datasource_count = $element->dataSource->count();
		for( $i = 0; $i < $datasource_count; $i++ )
		{
			$datasource = new DataSource( $element->dataSource[$i] );
			$this->_datasources[$datasource->get_name()] = $datasource;
		}

		$this->_document = new Document( $this, $element->document );
	}

	public function execute_import()
	{
		$this->_document->execute_import();
	}

	public function get_datasource( $name = null )
	{
		return $this->_datasources[(string)$name];
	}

	public function get_document()
	{
		return $this->_document;
	}
}

class DataSource
{
	protected $_connection = null;

	protected $_name = null;

	protected $_host = null;
	protected $_port = null;
	protected $_user = null;
	protected $_pass = null;
	protected $_db = null;
	protected $_params = null;
	

	public function __construct( SimpleXMLElement $element )
	{
		$name = (string)$element->attributes()->name;
		if( 0 !== strlen( $name ) )
		{
			$this->_name = $name;
		}

		if( 'com.mysql.jdbc.Driver' !== (string)$element->attributes()->driver )
		{
			throw new Exception( 'This prototype requires "com.mysql.jdbc.Driver"' );
		}

		$url = (string)$element->attributes()->url;
		$url = substr( $url, 5 );

		$url_parts = parse_url( $url );
		$this->_host = $url_parts['host'];
		$this->_db = substr( $url_parts['path'], 1 );

		if( isset( $url_parts['port'] ) )
		{
			$this->_port = (int)$url_parts['port'];
		}

		$user = (string)$element->attributes()->user;
		if( 0 !== strlen( $user ) )
		{
			$this->_user = $user;
		}

		$pass = (string)$element->attributes()->password;
		if( 0 !== strlen( $pass ) )
		{
			$this->_pass = $pass;
		}

		if( isset( $url_parts['query'] ) )
		{
			parse_str( $url_parts['query'], $params );
			$this->_params = $params;
		}
	}

	public function get_name()
	{
		return (string)$this->_name;
	}

	protected function is_connected()
	{
		return (bool)$this->_connection;
	}

	protected function connect()
	{
		$this->_connection = new PDO
		(
			sprintf
			(
				'mysql:host=%s;dbname=%s;port=%d',
				$this->_host,
				$this->_db,
				$this->_port ?: 3306
			),
			$this->_user,
			$this->_pass,
			array
			(
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"'
			)
		);
	}

	public function query( $query_str )
	{
		if( !$this->is_connected() )
		{
			$this->connect();
		}
		return $this->_connection->query( $query_str );
	}
}

class Document
{
	protected $_dataconfig = null;

	protected $_name = null;
	protected $_entities = array();

	protected $_meta = array();
	protected $_rows = array();
	protected $_queries = array();

	public function __construct( DataConfig $dataconfig, SimpleXMLElement $element )
	{
		$this->_dataconfig = $dataconfig;

		$name = (string)$element->attributes()->name;
		if( 0 !== strlen( $name ) )
		{
			$this->_name = $name;
		}
		
		$entity_count = $element->entity->count();
		for( $i = 0; $i < $entity_count; $i++ )
		{
			$entity = new Entity( $this, $element->entity[$i] );
			$this->_entities[$entity->get_name()] = $entity;
		}
	}

	public function get_dataconfig()
	{
		return $this->_dataconfig;
	}

	public function get_name()
	{
		return (string)$this->_name;
	}

	public function execute_import()
	{
		foreach( $this->_entities as $entity )
		{
			$entity->execute_import();
		}
	}

	public function get_root_entities()
	{
		return array_keys( $this->_rows );
	}

	public function get_rows_for( $entity )
	{
		return $this->_rows[$entity];
	}

	public function get_meta_for( $entity )
	{
		return $this->_meta[$entity];
	}

	public function set_query( Entity $entity, $query, $count = null )
	{
		$this->_queries[$entity->get_ident()][(string)$count] = $query;
	}

	public function get_query( $entity_ident, $count = null )
	{
		return isset( $this->_queries[$entity_ident][(string)$count] )
		     ? $this->_queries[$entity_ident][(string)$count]
		     : null;
	}

	public function set_data( $count, Entity $entity, array $record )
	{
		$count = (int)$count;

		$root = $entity->get_root()->get_ident();
		$ident = $entity->get_ident();

		if( !isset( $this->_rows[$root] ) )
		{
			$this->_meta[$root] = array();
			$this->_rows[$root] = array();
		}

		if( !isset( $this->_rows[$root][$count] ) )
		{
			$this->_rows[$root][$count] = array();
		}

		if( !isset( $this->_rows[$root][$count][$ident] ) )
		{
			$this->_meta[$root][$ident] = array();
			$this->_rows[$root][$count][$ident] = array();
		}
		
		foreach( $record as $key => $value )
		{
			if( !isset( $this->_rows[$root][$count][$ident][$key] ) )
			{
				$this->_rows[$root][$count][$ident][$key] = array();
			}

			$this->_meta[$root][$ident][$key] = true;
			$this->_rows[$root][$count][$ident][$key][] = $value;
		}
	}

}

class Entity
{
	protected $_document = null;
	protected $_parent = null;

	protected $_name = null;
	protected $_query = null;
	protected $_datasource = null;

	protected $_fields = array();
	protected $_entities = array();

	public function __construct( Document $document, SimpleXMLElement $element, Entity $parent = null )
	{
		$this->_document = $document;
		$this->_parent = $parent;

		$this->_name = (string)$element->attributes()->name;
		$this->_query = (string)$element->attributes()->query;

		$datasource = (string)$element->attributes()->dataSource;
		if( 0 !== strlen( $datasource ) )
		{
			$this->_datasource = $datasource;
		}

		$field_count = $element->field->count();
		for( $i = 0; $i < $field_count; $i++ )
		{
			$field = new Field( $element->field[$i] );
			$this->_fields[$field->get_name()] = $field;
		}

		$entity_count = $element->entity->count();
		for( $i = 0; $i < $entity_count; $i++ )
		{
			$entity = new Entity( $document, $element->entity[$i], $this );
			$this->_entities[$entity->get_name()] = $entity;
		}
	}

	public function get_document()
	{
		return $this->_document;
	}

	public function get_name()
	{
		return $this->_name;
	}

	public function get_ident()
	{
		$ident  = '';

		if( $this->_parent )
		{
			$ident .= $this->_parent->get_ident().SEP;
		}

		$ident .= $this->get_name();

		return $ident;
	}

	public function get_root()
	{
		return $this->_parent ? $this->_parent->get_root() : $this;
	}

	protected function prepare_query( $query_str, array $parent_data )
	{
		$pattern = '!\$\{([\w]+)\.([\w]+)\}!';

		/*
		preg_match_all
		(
			$pattern,
			$query_str,
			$matches,
			PREG_SET_ORDER
		);
		echo '<pre>'.__METHOD__.' @ '.__LINE__.' > '.print_r( $matches, true ).'</pre>';
		echo '<pre>'.__METHOD__.' @ '.__LINE__.' > '.print_r( $parent_data, true ).'</pre>';
		//*/

		$query_str = preg_replace_callback
		(
			$pattern,
			function( array $match ) use( $parent_data )
			{
				return $parent_data[$match[1]][$match[2]];
			},
			$query_str
		);

		return $query_str;
	}

	public function execute_import( $row = null, array $parent_data = array() )
	{
		$this->get_document()->set_query( $this, $this->_query );

		$query = $this->prepare_query( $this->_query, $parent_data );
		if( $row )
		{
			$this->get_document()->set_query( $this, $query, $row );
		}

		$datasource = $this->get_document()->get_dataconfig()->get_datasource( $this->_datasource );
		$result = $datasource->query( $query );
		
		$i = 1;
		while( $record = $result->fetch() )
		{
			$count = $row ?: $i;

			$this->get_document()->set_data( $count, $this, $record );

			foreach( $this->_entities as $entity )
			{
				$parent_data[$this->get_name()] = $record;
				$entity->execute_import( $count, $parent_data );
			}

			$i++;
		}
	}
}

class Field
{
	protected $_name = null;
	protected $_column = null;

	public function __construct( SimpleXMLElement $element )
	{
		$this->_name = (string)$element->attributes()->name;
		$this->_column = (string)$element->attributes()->column;
	}

	public function get_name()
	{
		return $this->_name;
	}

	public function get_column()
	{
		return $this->_column;
	}
}

$config_nr = isset( $_GET['config_nr'] ) ? (int)$_GET['config_nr'] : 1;
$config_file = __DIR__.'/../data/config-'.str_pad( $config_nr, 2, '0', STR_PAD_LEFT ).'.xml';
$schema_file = __DIR__.'/../data/dih-config.xsd';

header( 'Content-Type: text/html; Charset=UTF-8' );
libxml_use_internal_errors( true );

$config_xml = new DOMDocument();
$config_xml->load( $config_file );

$errors = libxml_get_errors();
if( !empty( $errors ) )
{
	include __DIR__.'/_error.php';
	die();
}

$config_valid = $config_xml->schemaValidate( $schema_file );
if( !$config_valid )
{
	$errors = libxml_get_errors();
	include __DIR__.'/_error.php';
	die();
}

$data_config = new DataConfig( simplexml_import_dom( $config_xml ) );

try
{
	$data_config->execute_import();
	echo '<hr><pre>#'.__LINE__.' : '.print_r( $data_config, true ).'</pre>';
}
catch( Exception $exception )
{
	include __DIR__.'/_exception.php';
	die();
}

include __DIR__.'/_result.php';