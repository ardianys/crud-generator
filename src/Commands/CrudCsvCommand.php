<?php

namespace Appzcoder\CrudGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

class CrudCsvCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:csv
                            {name : The name of the migration.}
                            {--schema= : The name of the schema.}
                            {--indexes= : The fields to add an index to.}
                            {--foreign-keys= : Foreign keys.}
                            {--pk=id : The name of the primary key.}
                            {--soft-deletes=no : Include soft deletes fields.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Csv';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return config('crudgenerator.custom_template')
        ? config('crudgenerator.path') . '/csv.stub'
        : __DIR__ . '/../stubs/csv.stub';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function getPath($name)
    {
        $name = str_replace($this->laravel->getNamespace(), '', $name);
        $datePrefix = date('Y_m_d_His');

        return database_path('/seeders/csv/') . $name . '.csv';
    }

    /**
     * Build the model class with the given name.
     *
     * @param  string  $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $tableName = $this->argument('name');
        $className = 'Create' . str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))) . 'Table';


        $schema = rtrim($this->option('schema'), ';');
        $fields = explode(';', $schema);

        $data = array();

        $header = 'id,';
        $body = '1,';

        if ($schema) {
            $x = 0;
            foreach ($fields as $field) {
                $fieldArray = explode('#', $field);
                // var_dump($fieldArray);
                $data[$x]['name'] = trim($fieldArray[0]);
                $data[$x]['type'] = trim($fieldArray[1]);
                $modifier = trim($fieldArray[2]);
                if ($modifier === 'unique') {
                    $datePrefix = date('Y_m_d_His');
                }


                $header .= $data[$x]['name'] . ',';

                if ($data[$x]['type'] === 'foreignId') {
                    $body   .= '1,';
                } elseif ($data[$x]['type'] === 'date') {
                    $body   .= '2022-10-10 00:00:00,';
                } elseif ($data[$x]['type'] === 'datetime') {
                    $body   .= '2022-10-10 10:10:10,';
                } elseif ($data[$x]['type'] === 'integer') {
                    $body   .= '20,';
                } elseif ($data[$x]['type'] === 'boolean') {
                    $body   .= 'true,';
                } else {
                    $body   .= $data[$x]['name'] . ',';
                }

                $data[$x]['modifier'] = '';

                $x++;
            }
        }

        $body = substr($body, 0, -1);
        $body_copy = substr($body, 1, strlen($body));
        for ($i=2; $i < 100; $i++) {
          $body = $body . "\n" . $i . $body_copy;
        }

        $header = substr($header, 0, -1); // lose the last comma

        return $this->replaceSchemaUp($stub, $header)
            ->replaceSchemaDown($stub, $body)
            ->replaceClass($stub, $className);
    }

    /**
     * Replace the schema_up for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaUp
     *
     * @return $this
     */
    protected function replaceSchemaUp(&$stub, $header)
    {
        $stub = str_replace('{{header}}', $header, $stub);

        return $this;
    }

    /**
     * Replace the schema_down for the given stub.
     *
     * @param  string  $stub
     * @param  string  $schemaDown
     *
     * @return $this
     */
    protected function replaceSchemaDown(&$stub, $body)
    {
        $stub = str_replace('{{body}}', $body, $stub);

        return $this;
    }
}
