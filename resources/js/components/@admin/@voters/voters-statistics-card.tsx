import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Users, UserCheck, UserX, GraduationCap } from 'lucide-react';

interface Voter {
    id: number;
    student_id: string;
    firstname: string;
    lastname: string;
    email: string;
    photo: string;
    course: string;
    year_level: number;
    has_voted: boolean;
    voted_at?: string;
    status: 'active' | 'inactive';
    created_at: string;
}

interface VotersStatisticsCardProps {
    voters: Voter[];
}

export function VotersStatisticsCard({ voters }: VotersStatisticsCardProps) {
    const totalVoters = voters.length;
    const activeVoters = voters.filter(voter => voter.status === 'active').length;
    const inactiveVoters = voters.filter(voter => voter.status === 'inactive').length;
    const votedVoters = voters.filter(voter => voter.has_voted).length;
    const votingRate = totalVoters > 0 ? Math.round((votedVoters / totalVoters) * 100) : 0;

    const stats = [
        {
            title: 'Total Voters',
            value: totalVoters,
            icon: Users,
            description: 'Registered voters',
            color: 'text-blue-600'
        },
        {
            title: 'Active Voters',
            value: activeVoters,
            icon: UserCheck,
            description: 'Eligible to vote',
            color: 'text-green-600'
        },
        {
            title: 'Voted',
            value: votedVoters,
            icon: GraduationCap,
            description: `${votingRate}% voting rate`,
            color: 'text-purple-600'
        },
        {
            title: 'Inactive',
            value: inactiveVoters,
            icon: UserX,
            description: 'Not eligible',
            color: 'text-red-600'
        }
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {stats.map((stat) => {
                const Icon = stat.icon;
                return (
                    <Card key={stat.title} className="hover:shadow-md transition-shadow duration-200">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-gray-600">
                                {stat.title}
                            </CardTitle>
                            <Icon className={`h-4 w-4 ${stat.color}`} />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-gray-900">{stat.value}</div>
                            <p className="text-xs text-gray-500 mt-1">
                                {stat.description}
                            </p>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}
