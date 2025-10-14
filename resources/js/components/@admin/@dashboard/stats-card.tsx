import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { type LucideIcon } from 'lucide-react';

interface StatsCardProps {
    title: string;
    value: number;
    icon: LucideIcon;
    description?: string;
    trend?: {
        value: number;
        isPositive: boolean;
    };
}

export function StatsCard({ title, value, icon: Icon, description, trend }: StatsCardProps) {
    return (
        <Card className="relative overflow-hidden">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">{title}</CardTitle>
                <Icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value.toLocaleString()}</div>
                {description && (
                    <p className="text-xs text-muted-foreground">{description}</p>
                )}
                {trend && (
                    <div className={`flex items-center text-xs ${
                        trend.isPositive ? 'text-green-600' : 'text-red-600'
                    }`}>
                        <span>{trend.isPositive ? '+' : ''}{trend.value}%</span>
                        <span className="ml-1">from last period</span>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
