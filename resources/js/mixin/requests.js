import axios from "axios";

export const Requests = {
  methods: {
    /**
     * XHR request
     * @param props
     * @returns {Promise<unknown>}
     */
    request(props) {
      if (!props.hasOwnProperty('url')) {
        throw new ReferenceError('The request requires url endpoint.');
      }

      // Check if method exists
      if (!props.hasOwnProperty("method")) {
        props.method = "get";
      }
      // Set default headers
      props.headers = Object.assign({
        accept: "application/json",
        "content-type": props.method === "patch" || props.method === "delete"
          ? "application/x-www-form-urlencoded"
          : "multipart/form-data"
      }, props.headers || {});

      return axios(props)
    }
  }
}