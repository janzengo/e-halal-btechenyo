"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart";
import { Line, LineChart, CartesianGrid, XAxis, YAxis } from "recharts";
import { Clock, TrendingUp } from "lucide-react";
import { VoteDistribution } from "@/data/analytics-data";

interface VoteDistributionCardProps {
  data: VoteDistribution[];
}

const chartConfig = {
  votes: {
    label: "Votes",
    color: "hsl(var(--chart-1))",
  },
};

export function VoteDistributionCard({ data }: VoteDistributionCardProps) {
  const peakHour = data.reduce((prev, current) => (prev.votes > current.votes) ? prev : current);
  const totalVotes = data.reduce((sum, item) => sum + item.votes, 0);

  return (
    <Card className="col-span-2">
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardTitle className="text-base font-medium">Vote Distribution</CardTitle>
          <CardDescription>
            Voting activity throughout the day
          </CardDescription>
        </div>
        <div className="flex items-center space-x-2">
          <Clock className="h-4 w-4 text-purple-600" />
          <span className="text-sm font-medium text-purple-600">{peakHour.hour}</span>
        </div>
      </CardHeader>
      <CardContent>
        <ChartContainer config={chartConfig} className="h-[300px] w-full">
          <LineChart
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
              dataKey="hour" 
              tickLine={false}
              axisLine={false}
              tickMargin={8}
            />
            <YAxis 
              tickLine={false}
              axisLine={false}
              tickMargin={8}
            />
            <ChartTooltip 
              content={<ChartTooltipContent />}
              formatter={(value, name, props) => [
                `${value} votes at ${props.payload.hour}`,
                chartConfig.votes.label
              ]}
            />
            <Line 
              type="monotone"
              dataKey="votes" 
              stroke="#22c55e" 
              strokeWidth={3}
              dot={{ fill: "#22c55e", strokeWidth: 1, r: 3 }}
              activeDot={{ r: 5, stroke: "#22c55e", strokeWidth: 2, fill: "#22c55e" }}
            />
          </LineChart>
        </ChartContainer>
      </CardContent>
    </Card>
  );
}
