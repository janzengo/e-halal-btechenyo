"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart";
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";
import { Target, TrendingUp } from "lucide-react";
import { PositionParticipation } from "@/data/analytics-data";

interface PositionParticipationCardProps {
  data: PositionParticipation[];
}

const chartConfig = {
  participationRate: {
    label: "Participation Rate",
    color: "hsl(var(--chart-1))",
  },
};

export function PositionParticipationCard({ data }: PositionParticipationCardProps) {
  const averageParticipation = data.reduce((sum, item) => sum + item.participationRate, 0) / data.length;

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardTitle className="text-base font-medium">Position Participation</CardTitle>
          <CardDescription>
            Voter participation rate by position
          </CardDescription>
        </div>
        <div className="flex items-center space-x-2">
          <Target className="h-4 w-4 text-blue-600" />
          <span className="text-2xl font-bold text-blue-600">{averageParticipation.toFixed(1)}%</span>
        </div>
      </CardHeader>
      <CardContent>
        <ChartContainer config={chartConfig} className="h-[300px] w-full">
          <BarChart
            data={data}
            margin={{
              top: 20,
              right: 30,
              left: 20,
              bottom: 5,
            }}
          >
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis 
              dataKey="position" 
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              angle={-45}
              textAnchor="end"
              height={80}
            />
            <YAxis 
              tickLine={false}
              axisLine={false}
              tickMargin={8}
              domain={[0, 100]}
            />
            <ChartTooltip 
              content={<ChartTooltipContent />}
              formatter={(value, name, props) => [
                `${value}% (${props.payload.voted}/${props.payload.totalVoters} voters)`,
                chartConfig.participationRate.label
              ]}
            />
            <Bar 
              dataKey="participationRate" 
              fill="#22c55e" 
              radius={4}
            />
          </BarChart>
        </ChartContainer>
      </CardContent>
    </Card>
  );
}
