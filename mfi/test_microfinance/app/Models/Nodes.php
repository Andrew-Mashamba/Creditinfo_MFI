<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nodes extends Model
{
    use HasFactory;

    /**
     * @var mixed|null
     */
    public mixed $NODE_DB_HOST;
    /**
     * @var mixed|null
     */
    public mixed $NODE_DB_PORT;
    /**
     * @var mixed|null
     */
    public mixed $NODE_DB_DATABASE;
    /**
     * @var mixed|null
     */
    public mixed $NODE_DB_USERNAME;
    /**
     * @var mixed|null
     */
    public mixed $NODE_DB_PASSWORD;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_TRANSACTION_TYPE;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_CLIENT_IDENTIFIER;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_SERVICE_IDENTIFIER;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_STATUS;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_DESCRIPTION;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_SENDER;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_RECEIVER;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_AMOUNT;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_DATE;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_REFERENCE;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_SECONDARY_REFERENCE;
    /**
     * @var mixed|null
     */
    public mixed $DB_TABLE_NAME;
    /**
     * @var mixed|null
     */
    public mixed $NODE_TYPE;
    /**
     * @var mixed|null
     */
    public mixed $QUERY;
    /**
     * @var mixed|string
     */
    public mixed $NODE_STATUS;
    /**
     * @var mixed|null
     */
    public mixed $DATA_SOURCE_TYPE;
    public mixed $NODE_NAME;
    protected $table = 'nodes';
    protected $guarded = [];
}
