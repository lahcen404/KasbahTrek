import { api } from './client';
import type { CreateTripReportPayload, CreateTripReportResponse, TripReport } from '../types/reports';

export async function createTripReport(payload: CreateTripReportPayload): Promise<TripReport> {
  const { data } = await api.post<CreateTripReportResponse>('/reports', payload);
  return data.report;
}
