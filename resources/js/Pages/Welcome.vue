<script
    setup>
import {
    Head,
    Link,
    useForm
} from '@inertiajs/inertia-vue3';
import {
    onMounted,
    reactive,
    ref,
    watch
} from "vue";
import {
    Inertia
} from "@inertiajs/inertia";

const form = useForm({
    csv: null,
});
const display = ref(false);
const stage = ref(0);
const validationBatchId = ref(null);

const showDialog = () => {
    console.log('dsfds');
    this.display = !display;
};

watch(()=> validationBatchId.value, () => {
    if(validationBatchId.value !== undefined && validationBatchId.value != null) {
        console.log('Batch ID: ' + validationBatchId.value);
        setupValidateStateStream();
    }
});

onMounted(() => {
    checkStage();
});

const setupValidateStateStream = () => {
    // Not a real URL, just using for demo purposes
    progessMessage.value = 'Validating Records...';
    let x = "http://scilifevue.test/api/validate_status_sse?batch_id=" + validationBatchId.value;
    let es = new EventSource(x);

    es.addEventListener('message', event => {
        console.log(event.data);
        let data = JSON.parse(event.data);
        progessMessage.value = data.message;
        progressTotal.value = parseInt(data.total);
        progressValue.value = parseInt(data.progress);
    }, false);

    es.addEventListener('error', event => {
        if (event.readyState == EventSource.CLOSED) {
            progessMessage.value = 'Validation Failed';
            progressValue.value = 0;
            validationBatchId.value = null;
            checkStage();
            es.close();
        }
    }, false);

    es.addEventListener('close', event => {
        let data = JSON.parse(event.data);
        progessMessage.value = 'Validation Completed';
        progressValue.value = 100;
        failedRecords.value = parseInt(data.failed_records);
        successRecords.value = parseInt(data.success_records);
        validationBatchId.value = null;
        checkStage();
        es.close();
    }, false);
}
const checkStage = () => {
    return axios.get(route('process-stage')).then((response) => {
        if (response.status === 200) {
            stage.value = response.data.stage;
        }
    });
};

const uploadRecordFile = (event) => {
    let formData = new FormData();
    formData.append('csv', event.files[0]);
    axios.post(route('load-records'), formData).then((response) => {
        if (response.status === 200) {
            checkStage();
            startValidatingRecords();
        }
    }).catch((error) => {
        console.log(error);
    });
}

const startValidatingRecords = (event) => {
    axios.get(route('validate-records')).then((response) => {
        if (response.status === 200) {
            progessMessage.value = 'Validating Records...';
            progressValue.value = 0;
            checkStage();
            validationBatchId.value = response.data.details.id;
        }
    }).catch((error) => {
        console.log(error);
    });
}

const progessMessage = ref('Validating Records...');
const progressValue = ref(0);
const progressTotal = ref(0);
const failedRecords = ref(0);
const successRecords = ref(0);

const validateStatus = (id) => {
    progessMessage.value = 'Validating Records...';
    let validateInterval = setInterval(() => {
        axios.get(route('validate-status'),{params: {id: id}}).then(response => {
            if (response.status === 200) {
                if (response.data.status === 'completed') {
                    progessMessage.value = 'Validation Completed';
                    progressValue.value = 100;
                    failedRecords.value = response.data.failed_records;
                    successRecords.value = response.data.success_records;
                    clearInterval(validateInterval);
                    checkStage();
                } else if (response.data.status === 'in-progress') {
                    progessMessage.value = response.data.message;
                    progressTotal.value = response.data.total;
                    progressValue.value = response.data.progress;
                } else if (response.data.status === 'failed') {
                    progessMessage.value = 'Validation Failed';
                    progressValue.value = 0;
                    clearInterval(validateInterval);
                    checkStage();
                }
            }
            progessMessage.value = response.data.message;
        })
    },1000)
}

const startInsertingRecords = (event) => {
    axios.get(route('insert-records')).then((response) => {
        if (response.status === 200) {
            progessMessage.value = 'Inserting Records...';
            progressValue.value = 0;
            checkStage();
            insertStatus(response.data.details.id)
        }
    }).catch((error) => {
        console.log(error);
    });
}

const insertStatus = (id) => {
    progessMessage.value = 'Inserting Records...';
    let insertInterval = setInterval(() => {
        axios.get(route('insert-status'),{params: {id: id}}).then(response => {
            if (response.status === 200) {
                if (response.data.status === 'completed') {
                    progessMessage.value = 'Records Inserted';
                    progressValue.value = 100;
                    clearInterval(insertInterval);
                    checkStage();
                } else if (response.data.status === 'in-progress') {
                    progessMessage.value = response.data.message;
                    progressTotal.value = response.data.total;
                    progressValue.value = response.data.progress;
                } else if (response.data.status === 'failed') {
                    progessMessage.value = 'Insert Failed';
                    progressValue.value = 0;
                    clearInterval(insertInterval);
                    checkStage();
                }
            }
            progessMessage.value = response.data.message;
        })
    },1000)
}

const startProcessingRecords = (event) => {
    axios.get(route('process-records')).then((response) => {
        if (response.status === 200) {
            console.log(response.data.details.id);
            progessMessage.value = 'Processing Records...';
            progressValue.value = 0;
            checkStage();
            processStatus(response.data.details.id);
        }
    }).catch((error) => {
        console.log(error);
    });
}

const processStatus = (id) => {

    let processInterval = setInterval(() => {
         axios.get(route('process-status'),{params: {id: id}}).then(response => {
            if (response.status === 200) {
                if (response.data.status === 'completed') {
                    progessMessage.value = 'Records Processed';
                    progressValue.value = 100;
                    clearInterval(processInterval);
                    checkStage();
                } else if (response.data.status === 'in-progress') {
                    progessMessage.value = response.data.message;
                    progressTotal.value = response.data.total;
                    progressValue.value = response.data.progress;
                } else if (response.data.status === 'failed') {
                    progessMessage.value = 'Processing Failed';
                    progressValue.value = 0;
                    clearInterval(processInterval);
                    checkStage();
                }
            }
            progessMessage.value = response.data.message;
        })
    },1000)
}

const resetState = (event) => {
    progressValue.value = 0;
    axios.get(route('reset-state')).then((response) => {
        if (response.status === 200) {
            window.location.reload();
            checkStage();
        }
    }).catch((error) => {
        console.log(error);
    });
}

const downloadProcessedCSV = (event) => {
    let url = route('download-processed-csv');
    window.location.href = url;
}


</script>

<template>
    <Head
        title="Welcome"/>
    <div
        class="relative flex items-top justify-center min-h-screen bg-gray-100 dark:bg-gray-900 sm:items-center sm:pt-0">
        <div
            class="w-full md:w-1/2 p-10">
            <div
                class="flex justify-center pt-8">
                <h1 class="text-3xl font-weight-bold dark:text-white">
                    Scilife
                    Records
                    Portal</h1>
            </div>

            <div
                class="mt-8 overflow-hidden shadow sm:rounded-lg space-y-12">
                <section
                    v-if="stage===0">
                    <span
                        class="relative flex dark:text-white m-2 justify-center">Upload CSV</span>

                    <FileUpload
                        :multiple="false"
                        id="csv"
                        name="csv"
                        :custom-upload="true"
                        @uploader="uploadRecordFile"
                        accept="text/csv">
                        <template
                            #empty></template>
                    </FileUpload>

                </section>
                <section
                    v-if="stage===1">
                    <span
                        class="relative flex dark:text-white m-2 justify-center">Validating Records</span>
                    <ProgressBar
                        v-bind:value="progressValue"
                        :total="100"/>
                    <span
                        class="text-sm mb-5 flex justify-center text-gray-900 dark:text-white" > {{progessMessage??''}}</span>
                </section>

                <section
                    v-if="stage===2">
                    <span
                        class="relative flex dark:text-white m-2 justify-center">Inserting Records</span>
                    <ProgressBar
                        v-bind:value="progressValue"
                        v-bind:total="progressTotal"/>

                    <div>Failed Records: {{failedRecords}}</div>
                    <div>Success Records: {{successRecords}}</div>
                    <span
                        class="text-sm mb-5 flex justify-center text-gray-900 dark:text-white">{{progessMessage??''}}</span>

                    <span
                        class="flex justify-center m-2">

                    <Button
                        @click="startInsertingRecords"
                        icon="pi pi-check"
                        label="Insert Records"></Button>
                    </span>
                </section>

                <section
                    v-if="stage===3">
                    <span
                        class="relative flex dark:text-white m-2 justify-center">Processing Records</span>
                    <ProgressBar
                        v-bind:value="progressValue"
                        v-bind:total="progressTotal"/>
                    <span
                        class="text-sm mb-5 flex justify-center text-gray-900 dark:text-white">{{progessMessage??''}}</span>

                    <span
                        class="flex justify-center m-2">

                    <Button
                        @click="startProcessingRecords"
                        icon="pi pi-check"
                        label="Process Records"></Button>
                    </span>
                </section>

                <section
                    v-if="stage===4">
                    <span
                        class="relative flex dark:text-white m-2 justify-center">All Records are validated and processed</span>

                    <span
                        class="flex justify-center m-2">

                    <Button
                        @click="downloadProcessedCSV"
                        icon="pi pi-check"
                        label="Download Processed CSV"></Button>
                    </span>
                </section>
                <section class="flex justify-center p-2">
                    <Button
                        @click="resetState"
                        icon="pi pi-check"
                        label="Reset State"></Button>
                </section>
            </div>
        </div>
    </div>
</template>

<style
    scoped>

section {
    padding: 1rem;
}

.bg-gray-100 {
    background-color: #f7fafc;
    background-color: rgba(247, 250, 252, var(--tw-bg-opacity));
}

.border-gray-200 {
    border-color: #edf2f7;
    border-color: rgba(237, 242, 247, var(--tw-border-opacity));
}

.text-gray-400 {
    color: #cbd5e0;
    color: rgba(203, 213, 224, var(--tw-text-opacity));
}

.text-gray-500 {
    color: #a0aec0;
    color: rgba(160, 174, 192, var(--tw-text-opacity));
}

.text-gray-600 {
    color: #718096;
    color: rgba(113, 128, 150, var(--tw-text-opacity));
}

.text-gray-700 {
    color: #4a5568;
    color: rgba(74, 85, 104, var(--tw-text-opacity));
}

.text-gray-900 {
    color: #1a202c;
    color: rgba(26, 32, 44, var(--tw-text-opacity));
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-gray-800 {
        background-color: #2d3748;
        background-color: rgba(45, 55, 72, var(--tw-bg-opacity));
    }

    .dark\:bg-gray-900 {
        background-color: #1a202c;
        background-color: rgba(26, 32, 44, var(--tw-bg-opacity));
    }

    .dark\:border-gray-700 {
        border-color: #4a5568;
        border-color: rgba(74, 85, 104, var(--tw-border-opacity));
    }

    .dark\:text-white {
        color: #fff;
        color: rgba(255, 255, 255, var(--tw-text-opacity));
    }

    .dark\:text-gray-400 {
        color: #cbd5e0;
        color: rgba(203, 213, 224, var(--tw-text-opacity));
    }
}
</style>
