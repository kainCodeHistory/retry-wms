<template>
    <app-layout>
        <template #header>
            <h4 class="font-semibold text-xl text-gray-800 leading-tight">
                儲位 - 單筆新增/調整
            </h4>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-4 bg-white border-b border-gray-200">
                        <wx-validation-messages
                            :display="validation.display"
                            :errors="validation.errors"
                            :title="validation.title"
                        />

                        <div>
                            <div class="mt-4">
                                <wx-label for="searchSku">
                                    <template
                                        ><sup class="text-red-500 font-bold"
                                            >*</sup
                                        >SKU</template
                                    >
                                </wx-label>
                                <wx-input
                                    id="searchSku"
                                    class="block mt-1 w-full"
                                    type="text"
                                    :value="searchSku"
                                    @update="val => (searchSku = val)"
                                    required
                                    autofocus
                                    ref="searchSku"
                                    @keypress="getLocations"
                                />
                            </div>

                            <div class="mt-2">
                                <div
                                    class="-mx-4 sm:-mx-8 px-4 sm:px-8 py-4 overflow-x-auto"
                                >
                                    <div
                                        class="
                      inline-block
                      min-w-full
                      shadow
                      rounded-lg
                      overflow-hidden
                    "
                                    >
                                        <table
                                            class="min-w-full leading-normal"
                                            style="width: 800px"
                                        >
                                            <thead>
                                                <tr>
                                                    <wx-table-header
                                                        value="#"
                                                    ></wx-table-header>
                                                    <wx-table-header
                                                        value="儲位"
                                                    ></wx-table-header>
                                                    <wx-table-header
                                                        value="預設料號SKU"
                                                    ></wx-table-header>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="(record,
                                                    row) in records"
                                                    :key="row"
                                                >
                                                    <wx-table-cell>
                                                        <template>
                                                            <wx-button
                                                                class="ml-3"
                                                                color="red"
                                                                @click="
                                                                    editItem(
                                                                        record
                                                                    )
                                                                "
                                                                type="button"
                                                                id="editButton"
                                                                >編輯</wx-button
                                                            >
                                                        </template>
                                                    </wx-table-cell>
                                                    <wx-table-cell
                                                        :value="record.location"
                                                    ></wx-table-cell>
                                                    <wx-table-cell
                                                        :value="record.material_sku"
                                                    ></wx-table-cell>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <wx-spinner :display="loader" />
        <wx-dialog-modal :show="edit">
            <template #content>
                <div>
                    <wx-label for="location" value="儲位" />
                    <wx-label id="location" :value="location" />
                </div>

                <div class="mt-4">
                    <wx-label for="editSku" value="預設sku" />
                    <wx-input
                        id="editSku"
                        type="text"
                        class="mt-1 block w-full"
                        :value="editSku"
                        @update="val => (editSku = val)"
                    />
                </div>
            </template>

            <template #footer>
                <wx-button
                    class="ml-3"
                    color="red"
                    @click="edit = !edit"
                    type="button"
                    id="cancelButton"
                    >取消</wx-button
                >
                <wx-button
                    class="ml-3"
                    color="red"
                    @click="editSelectItem(location, editSku)"
                    type="button"
                    id="editButton"
                    >送出</wx-button
                >
            </template>
        </wx-dialog-modal>
    </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";

import WxButton from "@/Components/Button";
import WxInput from "@/Components/Input";
import WxLabel from "@/Components/Label";
import WxSpinner from "@/Components/Spinner";
import WxTableCell from "@/Components/TableCell";
import WxTableHeader from "@/Components/TableHeader";
import WxValidationMessages from "@/Components/ValidationMessages";
import WxDialogModal from "@/Components/DialogModal";

import WxCommon from "@/Mixins/Common";

export default {
    name: "locationCRUD",

    mixins: [WxCommon],

    components: {
        AppLayout,
        WxButton,
        WxInput,
        WxLabel,
        WxSpinner,
        WxTableCell,
        WxTableHeader,
        WxValidationMessages,
        WxDialogModal
    },

    data() {
        return {
            edit: false,
            searchSku: "",
            location: "",
            storageBox: "",
            editSku: "",
            records: []
        };
    },

    beforeRouteEnter(to, from, next) {
        if (!window.WMS.isLoggedIn) {
            window.location.href = "/login";
        }

        next();
    },

    methods: {
        async getLocations(e) {
            if (
                e.key.toUpperCase() === "ENTER" &&
                e.target.value.trim().length > 0
            ) {
                try {
                    this.loader = true;

                    await this.axios.get("/sanctum/csrf-cookie");
                    const result = await this.axios.get(
                        `/api/locations/${e.target.value
                            .trim()
                            .toUpperCase()}`
                    );

                    this.records = result.data.records;
                    this.searchSku = "";
                    this.loader = false;
                } catch (error) {
                    this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
                    this.reset();
                    this.loader = false;
                }
            }
        },

        async editItem(record) {
            this.edit = true;
            this.location = record.location;
            this.editSku = record.material_sku;
        },

        async editSelectItem(location, editSku) {
            try {
                await this.axios.get("/sanctum/csrf-cookie");
                const result = await this.axios.post("/api/locations", {
                    location: location,
                    sku: editSku,
                    oldSku:this.records[0].material_sku
                });

                const rowIndex = this.records.findIndex(
                    record => record.location === location
                );
                this.records[rowIndex].material_sku = editSku;

                this.displayValidation(true, [], "更改成功");
            } catch (error) {
                this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
            }
            this.edit = false;
        },

        reset() {
            this.records = [];
            this.searchSku = "";
        }
    }
};
</script>
