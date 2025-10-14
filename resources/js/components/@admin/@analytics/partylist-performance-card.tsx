"use client"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart";
import { Pie, PieChart, Cell } from "recharts";
import { Users, Trophy } from "lucide-react";
import { PartylistPerformance } from "@/data/analytics-data";

interface PartylistPerformanceCardProps {
  data: PartylistPerformance[];
}

const chartConfig = {
  totalVotes: {
    label: "Total Votes",
    color: "hsl(var(--chart-1))",
  },
};

const COLORS = ["#22c55e", "#16a34a", "#15803d", "#166534"];

export function PartylistPerformanceCard({ data }: PartylistPerformanceCardProps) {
  const totalVotes = data.reduce((sum, item) => sum + item.totalVotes, 0);
  const leadingPartylist = data.reduce((prev, current) => (prev.percentage > current.percentage) ? prev : current);

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <div className="space-y-1">
          <CardTitle className="text-base font-medium">Partylist Performance</CardTitle>
          <CardDescription>
            Vote distribution by political parties
          </CardDescription>
        </div>
        <div className="flex items-center space-x-2">
          <Trophy className="h-4 w-4 text-yellow-600" />
          <span className="text-sm font-medium text-yellow-600">{leadingPartylist.partylist}</span>
        </div>
      </CardHeader>
      <CardContent>
        <div className="grid grid-cols-2 gap-4">
          <div className="space-y-2">
            <ChartContainer config={chartConfig} className="h-[200px] w-full">
              <PieChart>
                <ChartTooltip content={<ChartTooltipContent />} />
                <Pie
                  data={data}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ partylist, percentage }) => `${partylist}: ${percentage}%`}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="totalVotes"
                >
                  {data.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
              </PieChart>
            </ChartContainer>
          </div>
          <div className="space-y-4">
            {data.map((partylist, index) => (
              <div key={partylist.partylist} className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <div 
                    className="w-3 h-3 rounded-full" 
                    style={{ backgroundColor: COLORS[index % COLORS.length] }}
                  />
                  <span className="text-sm font-medium">{partylist.partylist}</span>
                </div>
                <div className="text-right">
                  <div className="text-sm font-bold">{partylist.totalVotes} votes</div>
                  <div className="text-xs text-muted-foreground">{partylist.percentage}%</div>
                </div>
              </div>
            ))}
            <div className="pt-2 border-t">
              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">Total Votes:</span>
                <span className="font-bold">{totalVotes}</span>
              </div>
              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">Candidates:</span>
                <span className="font-bold">{data.reduce((sum, item) => sum + item.candidates, 0)}</span>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
