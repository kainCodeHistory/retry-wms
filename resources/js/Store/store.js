import Vue from 'vue';
import Vuex from 'vuex';
import axios from 'axios'

Vue.use(Vuex);

const store = new Vuex.Store({
    state: {
        employeeNo: JSON.parse(localStorage.getItem('employee'))
    },
    actions: {
        setEmployee (context, employee) {
            localStorage.setItem('employee', employee)
          },

        clearEmployee (context) {
            localStorage.removeItem('employee')
          },
        setOrderList (context , orderList){
            localStorage.setItem('orderList', orderList)
        },
        clearOrderList (context) {
            localStorage.removeItem('orderList')
          },

    },
    mutations: {

    }
})
export default store;
