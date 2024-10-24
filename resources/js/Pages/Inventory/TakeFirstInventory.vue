<template>
    <app-layout>
        <template #header>
            <h4 class="font-semibold text-xl text-gray-800 leading-tight">
                盤點 - 初次盤點
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
                                <wx-label for="warehouse">
                                    <template
                                        ><sup class="text-red-500 font-bold"
                                            >*</sup
                                        >
                                        倉別</template
                                    >
                                </wx-label>
                                <template>
                                    <div id="app">
                                        <WxSelection
                                            class="block mt-1 w-full"
                                            :options="[
                                                {
                                                    id: 1,
                                                    name: '烏日-撿料倉/預備倉'
                                                }
                                            ]"
                                            :default_option="'請選擇倉別'"

                                            id="warehouse"
                                            :value="warehouse"
                                            @update="(val) => (warehouse = val)"
                                        />
                                    </div>
                                </template>
                            </div>

                            <div class="mt-4">
                                <wx-label for="box">
                                    <template
                                        ><sup class="text-red-500 font-bold"
                                            >*</sup
                                        >
                                        箱號條碼</template
                                    >
                                </wx-label>
                                <wx-input
                                    id="box"
                                    class="block mt-1 w-full"
                                    type="text"
                                    :value="box"
                                    @update="val => (box = val)"
                                    required
                                    autofocus
                                    @keypress="getBox"
                                    ref="box"
                                />
                            </div>

                            <div class="mt-4">
                                <wx-label for="location">
                                    <template
                                        ><sup class="text-red-500 font-bold"
                                            >*</sup
                                        >
                                        儲位位置</template
                                    >
                                </wx-label>
                                <wx-input
                                    id="location"
                                    class="block mt-1 w-full"
                                    type="text"
                                    :value="location"
                                    @update="val => (location = val)"
                                    required
                                    autofocus
                                    :readonly="true"
                                />
                            </div>

                            <div class="mt-4">
                                <wx-label for="sku"
                                    ><template
                                        ><sup class="text-red-500 font-bold"
                                            >*</sup
                                        >
                                        SKU</template
                                    ></wx-label
                                >
                                <wx-input
                                    id="sku"
                                    class="block mt-1 w-full"
                                    type="text"
                                    :value="sku"
                                    @update="val => (sku = val)"
                                    required
                                    autofocus
                                    @keypress="getMaterialName"
                                />
                            </div>

                            <div class="mt-2">
                                <wx-label :value="materialName"></wx-label>
                            </div>

                            <div class="mt-4">
                                <wx-label for="firstQuantity"
                                    ><template
                                        ><sup class="text-red-500 font-bold"
                                            >*</sup
                                        >
                                        初盤數量</template
                                    ></wx-label
                                >
                                <wx-input
                                    id="firstQuantity"
                                    class="block mt-1 w-full"
                                    type="number"
                                    :value="firstQuantity"
                                    @update="val => (firstQuantity = val)"
                                    required
                                    autofocus
                                />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <wx-button
                                    class="ml-3"
                                    color="blue"
                                    @click="updateQuantity"
                                    >儲存</wx-button
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <wx-spinner :display="loader" />
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
import WxSelection from "@/Components/Selection";

import WxCommon from "@/Mixins/Common";

export default {
    name: "pickingAreaRefillAddLocation",

    mixins: [WxCommon],

    components: {
        AppLayout,
        WxButton,
        WxInput,
        WxLabel,
        WxSpinner,
        WxTableCell,
        WxTableHeader,
        WxSelection,
        WxValidationMessages
    },

    data() {
        return {
            box: "",
            location: "",
            materialName: "",
            warehouse: "",
            firstQuantity: "",
            sku: ""
        };
    },

    beforeRouteEnter(to, from, next) {
        if (!window.WMS.isLoggedIn) {
            window.location.href = "/login";
        }

        next();
    },

    methods: {
        async getBox(e) {
            if (
                e.key.toUpperCase() === "ENTER" &&
                e.target.value.trim().length > 0
            ) {
                try {
                    this.loader = true;

                    await this.axios.get("/sanctum/csrf-cookie");
                    const result = await this.axios.get(
                        `/api/inventory/${encodeURIComponent(
                            e.target.value.trim()
                        )}/${document.getElementById("warehouse").value}`
                    );

                    this.warehouse = result.data.warehouse;
                    this.location = result.data.location;
                    this.materialName = result.data.materialName;
                    this.quantity = result.data.quantity;
                    this.sku = result.data.sku;
                    this.box = result.data.box;

                    this.loader = false;
                    if (result.data.has_error) {
                        this.displayValidation(
                            result.data.has_error,
                            [
                                result.data.alreadyBox,
                                "初盤數量：" + result.data.firstQuantity
                            ],
                            "注意"
                        );
                    } else {
                        this.displayValidation(false);
                    }
                } catch (error) {
                    this.reset();
                    this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
                    this.loader = false;
                }
            }
        },

        async getMaterialName(e) {
            if (
                e.key.toUpperCase() === "ENTER" &&
                e.target.value.trim().length > 3
            ) {
                try {
                    this.loader = true;

                    await this.axios.get("/sanctum/csrf-cookie");
                    const result = await this.axios.get(
                        `/api/material/${e.target.value.trim()}`
                    );

                    this.materialName = result.data.name;
                    this.loader = false;
                } catch (error) {
                    this.loader = false;
                }

                this.displayValidation(false);
            }
        },

        reset() {
            this.location = "";
            this.materialName = "";
            this.firstQuantity = "";
            this.sku = "";
            this.box = "";
        },

        async updateQuantity() {
            try {
                this.loader = true;

                await this.axios.get("/sanctum/csrf-cookie");
                const result = await this.axios.post(
                    "/api/firstInventory/quantity",
                    {
                        box: this.box,
                        location: this.location,
                        warehouse: warehouse.value,
                        firstQuantity: this.firstQuantity
                    }
                );

                this.reset();
                this.$refs.box.focus();
                this.displayValidation(true, [], "儲存成功。");
                this.loader = false;
            } catch (error) {
                this.displayValidation(true, this.handleAxiosResponseErrorMessages(error), "錯誤訊息。");
                this.loader = false;
            }
        }
    }
};
</script>
