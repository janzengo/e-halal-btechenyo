import React, { useState, useEffect } from 'react';
import { ChevronDown, ChevronUp } from 'lucide-react';
import AOS from 'aos';
import 'aos/dist/aos.css';

interface FAQSectionProps {
    isLoading?: boolean;
}

const faqData = [
    {
        question: "How do I vote in the student elections?",
        answer: "Simply log in with your student number and password, verify your identity with the OTP sent to your registered phone number, select your preferred candidates for each position, and submit your vote. You'll receive a digital receipt confirming your vote was recorded."
    },
    {
        question: "Is my vote secure and anonymous?",
        answer: "Yes, absolutely. Your vote won't be referenced and linked to you once you submit it, so no one, even the administrators, will know your casted votes. The system is designed to ensure complete anonymity."
    },
    {
        question: "Can I vote from my mobile phone?",
        answer: "Yes! Our platform is fully responsive and mobile-friendly. You can vote from any device - smartphone, tablet, or computer - as long as you have internet access."
    },
    {
        question: "What if I don't receive the OTP?",
        answer: "If you don't receive the OTP within 5 minutes, please check your spam folder first. If still not received, contact the Student Affairs office or try requesting a new OTP. Make sure your phone number is correctly registered in the system."
    },
    {
        question: "Can I change my vote after submitting?",
        answer: "No, once you submit your vote, it cannot be changed. This ensures the integrity of the election process. Please review your selections carefully before submitting."
    },
    {
        question: "When will the election results be announced?",
        answer: "Results are displayed in real-time as votes are counted. Official results will be announced after the voting period ends and all votes have been verified by the election committee."
    },
    {
        question: "What if I encounter technical issues while voting?",
        answer: "If you experience any technical problems, please contact the IT support team immediately at admin@ehalal.tech or visit the Student Affairs office. We have technical support available during voting hours."
    },
    {
        question: "Who can vote in the student elections?",
        answer: "All currently enrolled BTECHenyos are eligible to vote. The admintrator will register the students using the student numbers."
    }
];

export default function FAQSection({ isLoading = false }: FAQSectionProps) {
    const [openItems, setOpenItems] = useState<number[]>([]);

    // Initialize AOS for this component
    useEffect(() => {
        AOS.init({
            duration: 600,
            once: true,
            offset: 100,
        });
    }, []);

    const toggleItem = (index: number) => {
        setOpenItems(prev => 
            prev.includes(index) 
                ? prev.filter(item => item !== index)
                : [...prev, index]
        );
    };

    if (isLoading) {
        return (
            <section id="faq" className="py-20">
                <div className="max-w-4xl mx-auto px-6">
                    <div className="text-center mb-16">
                        <div className="h-9 w-64 bg-gray-300 rounded mx-auto mb-4 animate-pulse"></div>
                        <div className="h-6 w-96 bg-gray-300 rounded mx-auto animate-pulse"></div>
                    </div>
                    <div className="space-y-4">
                        {[1, 2, 3, 4, 5, 6].map((item) => (
                            <div key={item} className="bg-white p-6 rounded-lg border border-gray-200">
                                <div className="h-6 w-3/4 bg-gray-300 rounded animate-pulse mb-2"></div>
                                <div className="h-4 w-full bg-gray-300 rounded animate-pulse"></div>
                            </div>
                        ))}
                    </div>
                </div>
            </section>
        );
    }

    return (
        <section id="faq" className="py-20">
            <div className="max-w-4xl mx-auto px-6">
                <div className="text-center mb-16" data-aos="fade-up">
                    <h2 className="text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h2>
                    <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                        Everything you need to know about the E-Halal BTECHenyo voting system
                    </p>
                </div>

                <div className="space-y-4">
                    {faqData.map((faq, index) => (
                        <div 
                            key={index}
                            className="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                            data-aos="fade-up"
                            data-aos-delay={`${(index + 1) * 100}`}
                        >
                            <button
                                onClick={() => toggleItem(index)}
                                className="w-full px-6 py-4 text-left flex justify-between items-center hover:bg-gray-50 transition-colors"
                            >
                                <h3 className="text-lg font-semibold text-gray-900 pr-4">
                                    {faq.question}
                                </h3>
                                {openItems.includes(index) ? (
                                    <ChevronUp className="w-5 h-5 text-gray-500 flex-shrink-0" />
                                ) : (
                                    <ChevronDown className="w-5 h-5 text-gray-500 flex-shrink-0" />
                                )}
                            </button>
                            
                            {openItems.includes(index) && (
                                <div className="px-6 pb-4">
                                    <p className="text-gray-600 leading-relaxed mt-4">
                                        {faq.answer}
                                    </p>
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
