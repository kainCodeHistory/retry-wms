<template>
  <div>
    <wx-banner />

    <div class="min-h-screen bg-gray-100">
      <nav class="bg-white border-b border-gray-100">
        <!-- Primary Navigation Menu -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex justify-between h-16">
            <div class="flex">
              <!-- Logo -->
              <div class="flex-shrink-0 flex items-center">
                <a href="/">
                  <wx-application-mark class="block h-9 w-auto" />
                </a>
              </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
              <!--倉庫-->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="60">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:bg-gray-50 hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition"
                      >
                        倉庫

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="w-60">
                      <template> 
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          入庫
                        </div>
                        <wx-dropdown-link
                          href="/binding-picking-box"
                          :active="currentRoute === 'b2b5fBindingPickingBox'"
                        >
                          綁定撿料箱
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/binding-location"
                          :active="currentRoute === 'b2b5fBindingLocation'"
                        >
                          儲位綁定
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/binding-XB-location"
                          :active="currentRoute === 'b2b5fBindingXBLocation'"
                        >
                          綁定特殊儲位
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/item-inventory?event=transfer_input"
                          :active="currentRoute === 'b2b5fItemInventory'"
                        >
                          品項轉入
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          品項出庫
                        </div>
                        <wx-dropdown-link
                          href="/item-inventory?event=transfer_output"
                          :active="currentRoute === 'b2b5fItemInventory'"
                        >
                          品項出庫
                        </wx-dropdown-link>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                          查詢
                        </div>
                        <wx-dropdown-link
                          href="/ean-sku-search"
                          :active="currentRoute === 'b2b5fEanSkuSearch'"
                        >
                          EAN/SKU 查詢
                        </wx-dropdown-link>
                        <wx-dropdown-link
                          href="/stock-logs"
                          :active="currentRoute === 'b2bStockLogs'"
                        >
                          查詢總表
                        </wx-dropdown-link>
                      </template>
                    </div>
                  </template>
                </wx-dropdown>
              </div>

              <!-- Settings Dropdown -->
              <div class="ml-3 relative">
                <wx-dropdown align="right" width="48">
                  <template #trigger>
                    <button
                      v-if="false"
                      class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition"
                    >
                      <img
                        class="h-8 w-8 rounded-full object-cover"
                        :src="user.profile_photo_url"
                        :alt="user.name"
                      />
                    </button>

                    <span v-else class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition"
                      >
                        {{ user.name }}

                        <svg
                          class="ml-2 -mr-0.5 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <!-- Authentication -->
                    <form @submit.prevent="logout">
                      <wx-dropdown-link as="button"> 登出 </wx-dropdown-link>
                    </form>
                  </template>
                </wx-dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div
          :class="{
            block: showingNavigationDropdown,
            hidden: !showingNavigationDropdown,
          }"
          class="sm:hidden"
        >
          <!-- Responsive Settings Options -->
          <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="flex items-center px-4">
              <div v-if="false" class="flex-shrink-0 mr-3">
                <img
                  class="h-10 w-10 rounded-full object-cover"
                  :src="user.profile_photo_url"
                  :alt="user.name"
                />
              </div>

              <div>
                <div class="font-medium text-base text-gray-800">
                  {{ user.name }}
                </div>
                <div class="font-medium text-sm text-gray-500">
                  {{ user.email }}
                </div>
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <!-- Authentication -->
              <form method="POST" @submit.prevent="logout">
                <wx-responsive-nav-link as="button">
                  登出
                </wx-responsive-nav-link>
              </form>

              <template>
                <div class="border-t border-gray-200"></div>
                <div class="block px-4 py-2 text-xs text-gray-400">倉庫</div>
                <wx-responsive-nav-link
                  href="/binding-picking-box"
                  :active="currentRoute === 'b2b5fBindingPickingBox'"
                >
                  綁定撿料箱
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/binding-location"
                  :active="currentRoute === 'b2b5fBindingLocation'"
                >
                  綁定儲位
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/binding-XB-location"
                  :active="currentRoute === 'b2b5fBindingXBLocation'"
                >
                  綁定特殊儲位
                </wx-responsive-nav-link>

                <wx-responsive-nav-link
                  href="/item-inventory?event=transfer_input"
                  :active="currentRoute === 'b2b5fItemInventory'"
                >
                  品項轉入
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/item-inventory?event=transfer_output"
                  :active="currentRoute === 'b2b5fItemInventory'"
                >
                  品項出庫
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/ean-sku-search"
                  :active="currentRoute === 'b2b5fEanSkuSearch'"
                >
                 EAN/SKU 查詢
                </wx-responsive-nav-link>
                <wx-responsive-nav-link
                  href="/stock-logs"
                  :active="currentRoute === 'b2bStockLogs'"
                >
                 查詢總表
                </wx-responsive-nav-link>
              </template>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
          <slot name="header"></slot>
        </div>
      </header>

      <!-- Page Content -->
      <main>
        <slot></slot>
      </main>
    </div>
  </div>
</template>

<script>
import WxApplicationMark from "@/Components/ApplicationMark";
import WxBanner from "@/Components/Banner";
import WxDropdown from "@/Components/Dropdown";
import WxDropdownLink from "@/Components/DropdownLink";
import WxNavLink from "@/Components/NavLink";
import WxResponsiveNavLink from "@/Components/ResponsiveNavLink";

export default {
  components: {
    WxApplicationMark,
    WxBanner,
    WxDropdown,
    WxDropdownLink,
    WxNavLink,
    WxResponsiveNavLink,
  },

  data() {
    return {
      showingNavigationDropdown: false,
      user: null,
    };
  },

  created() {
    if (window.WMS.user) {
      this.user = window.WMS.user;
    }
  },

  computed: {
    currentRoute() {
      return this.$route.name;
    },
  },

  methods: {
    async logout(e) {
      e.preventDefault();
      try {
        await this.axios.get("/sanctum/csrf-cookie");
        await this.axios.post("/api/logout");

        window.location.href = "/login";
      } catch (error) {
        console.log(error);
      }
    },
  },
};
</script>
