export const storage = {
  get: key => JSON.parse(sessionStorage.getItem(key)),
  set: (key, val) => sessionStorage.setItem(key, JSON.stringify(val))
}