import { api, setAuthToken } from './client';

export type LoginUser = {
  fullname: string;
  role: string;
};

type LoginResponse = {
  message: string;
  token: string;
  user: LoginUser;
};


export async function login(email: string, password: string): Promise<LoginUser> {
  const { data } = await api.post<LoginResponse>('/login', { email, password });
  setAuthToken(data.token);
  return data.user;
}
