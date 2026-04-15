<?php

namespace App\Http\Controllers;

use Statamic\Facades\YAML;
use Statamic\View\View;

class PanelScreenshotController extends Controller
{
    public function show(string $handle)
    {
        $fieldsetPath = resource_path("fieldsets/{$handle}.yaml");

        abort_if(! file_exists($fieldsetPath), 404, "No fieldset found for panel: {$handle}");

        $fieldset = YAML::parse(file_get_contents($fieldsetPath));
        $dummy    = $this->buildDummyData($fieldset['fields'] ?? []);
        $dummy['type'] = $handle;

        return View::make('__panel-screenshot')
            ->with(['_panel_handle' => $handle] + $dummy);
    }

    /**
     * Recursively build dummy data from a fieldset's fields array.
     * Handles all field types used across panel fieldsets, including
     * nested fieldset imports (e.g. handle: link, field.import: button).
     */
    private function buildDummyData(array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            // Top-level fieldset import — no handle, just `import: fieldset-name`
            // Merges the imported fieldset's fields directly into the current scope.
            if (isset($field['import']) && ! isset($field['handle'])) {
                $importPath = resource_path("fieldsets/{$field['import']}.yaml");
                if (file_exists($importPath)) {
                    $imported = YAML::parse(file_get_contents($importPath));
                    $data = array_merge($data, $this->buildDummyData($imported['fields'] ?? []));
                }
                continue;
            }

            $handle = $field['handle'] ?? null;
            if (! $handle) continue;

            $def  = $field['field'] ?? [];
            $type = $def['type'] ?? null;

            // Field-level import — handle: foo, field.import: fieldset-name
            // Produces a nested array (e.g. link: { link_type: ..., url: ... }).
            if (isset($def['import'])) {
                $importPath = resource_path("fieldsets/{$def['import']}.yaml");
                if (file_exists($importPath)) {
                    $imported = YAML::parse(file_get_contents($importPath));
                    $data[$handle] = $this->buildDummyData($imported['fields'] ?? []);
                } else {
                    $data[$handle] = [];
                }
                continue;
            }

            // UI-only field types — skip
            if ($type === 'section') continue;

            // Use the field's configured default value when available
            if (array_key_exists('default', $def)) {
                $data[$handle] = $def['default'];
                continue;
            }

            $data[$handle] = match ($type) {
                'text'         => 'Lorem ipsum dolor sit amet',
                'textarea'     => "Lorem ipsum dolor sit amet\nconsectetur adipiscing elit",
                'bard'         => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>',
                'button_group' => array_key_first($def['options'] ?? []),
                'select'       => array_key_first($def['options'] ?? []),
                'toggle'       => false,
                'integer'      => 1,
                'assets'       => null,
                'video'        => null,
                'entries'      => null,
                'replicator'   => [],
                default        => null,
            };
        }

        return $data;
    }
}
