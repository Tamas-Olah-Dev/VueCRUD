<model-manager index-url="{!! $indexUrl !!}"
               details-url="{!! $detailsUrl !!}"
               create-url="{!! $createUrl !!}"
               edit-url="{!! $editUrl !!}"
               store-url="{!! $storeUrl !!}"
               update-url="{!! $updateUrl !!}"
               delete-url="{!! $deleteUrl !!}"
               ajax-operations-url="{!! $ajaxOperationsUrl !!}"
               :allow-operations="{{ $allowOperations ? 'true' : 'false' }}"
               :allow-adding="{{ $allowAdding ? 'true' : 'false' }}"
               :use-sweet-alert="false"
               subject-name="{{ $subjectName }}"
               :class-overrides="{{ json_encode([
                    'edit-form-step-head' => 'portlet-head'
               ]) }}"
               :icon-classes="{{ json_encode([
                            "filter" => "mdi mdi-magnify",
                            "list" => "mdi mdi-format-list-bulleted",
                            "leftArrow" => "mdi mdi-arrow-left-thick",
                            "rightArrow" => "mdi mdi-arrow-right-thick"
                        ]) }}"
></model-manager>
