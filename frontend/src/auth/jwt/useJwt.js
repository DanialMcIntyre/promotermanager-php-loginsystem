import useJwt from '@core/auth/jwt/useJwt'
import axios from '@axios'
import config from './jwtLocalConfig'

const { jwt } = useJwt(axios, config)
console.log(config);
console.log(axios);
export default jwt